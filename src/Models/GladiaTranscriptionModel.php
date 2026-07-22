<?php

declare(strict_types=1);

namespace AiSdk\Gladia\Models;

use AiSdk\ContentSource;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\TranscriptionModelInterface;
use AiSdk\Gladia\GladiaOptions;
use AiSdk\Requests\TranscriptionRequest;
use AiSdk\Responses\TranscriptionResponse;
use AiSdk\Results\TranscriptData;
use AiSdk\Results\TranscriptSegment;
use AiSdk\Support\Usage;
use AiSdk\Utils\Support\Url;

final class GladiaTranscriptionModel extends BaseModel implements TranscriptionModelInterface
{
    public function __construct(private readonly string $modelId, private readonly GladiaOptions $options) {}

    public function provider(): string
    {
        return GladiaOptions::PROVIDER_NAME;
    }
    public function modelId(): string
    {
        return $this->modelId;
    }

    public function transcribe(TranscriptionRequest $request): TranscriptionResponse
    {
        $runner = $this->runner($this->options->sdk);
        $audioUrl = $request->audio->source() === ContentSource::Url ? $request->audio->url() : $this->uploadAudio($request);
        $submitted = $runner->postJson(Url::joinPath($this->options->baseUrl, '/v2/pre-recorded'), array_replace(['audio_url' => $audioUrl, 'model' => $this->modelId], $request->providerOptionsFor($this->provider())), $this->options->authHeaders(), $this->provider());
        $id = (string) ($submitted['id'] ?? '');
        if ($id === '') {
            throw new \RuntimeException('Gladia did not return a transcription id.');
        }

        do {
            if ($this->options->pollingIntervalMs > 0) {
                usleep($this->options->pollingIntervalMs * 1000);
            }
            $payload = $runner->getJson(Url::joinPath($this->options->baseUrl, '/v2/pre-recorded/' . rawurlencode($id)), $this->options->authHeaders(), $this->provider());
        } while (in_array($payload['status'] ?? '', ['queued', 'processing'], true));
        if (($payload['status'] ?? '') === 'error') {
            throw new \RuntimeException('Gladia transcription failed: ' . (string) ($payload['error']['message'] ?? 'unknown error'));
        }

        $transcription = is_array($payload['result']['transcription'] ?? null) ? $payload['result']['transcription'] : [];
        $segments = [];
        foreach ((array) ($transcription['utterances'] ?? []) as $utterance) {
            if (is_array($utterance)) {
                $segments[] = new TranscriptSegment((string) ($utterance['text'] ?? ''), (float) ($utterance['start'] ?? 0), (float) ($utterance['end'] ?? 0), isset($utterance['speaker']) ? (string) $utterance['speaker'] : null);
            }
        }
        $languages = $transcription['languages'] ?? [];
        return new TranscriptionResponse(new TranscriptData((string) ($transcription['full_transcript'] ?? ''), is_array($languages) ? (string) ($languages[0] ?? '') : null, isset($payload['result']['metadata']['audio_duration']) ? (float) $payload['result']['metadata']['audio_duration'] : null, $segments), new Usage(), $payload, ['gladia' => ['id' => $id]]);
    }

    private function uploadAudio(TranscriptionRequest $request): string
    {
        $sdk = $this->options->sdk ?? \AiSdk\Generate::sdk();
        $boundary = 'aisdk-' . bin2hex(random_bytes(12));
        $audio = (string) base64_decode((string) $request->audio->base64Data(), true);
        $body = "--{$boundary}\r\nContent-Disposition: form-data; name=\"audio\"; filename=\"audio\"\r\nContent-Type: application/octet-stream\r\n\r\n{$audio}\r\n--{$boundary}--\r\n";
        $upload = $sdk->requestFactory->createRequest('POST', Url::joinPath($this->options->baseUrl, '/v2/upload'))->withBody($sdk->streamFactory->createStream($body))->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);
        foreach ($this->options->authHeaders() as $name => $value) {
            $upload = $upload->withHeader($name, $value);
        }
        $payload = json_decode((string) $this->runner($this->options->sdk)->sendRequest($upload, $this->provider())->getBody(), true);
        $url = is_array($payload) ? (string) ($payload['audio_url'] ?? '') : '';
        if ($url === '') {
            throw new \RuntimeException('Gladia did not return an uploaded audio URL.');
        }
        return $url;
    }
}
