<?php

declare(strict_types=1);
use AiSdk\Content;
use AiSdk\Generate;
use AiSdk\Gladia;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

afterEach(function (): void {
    Generate::reset();
    Gladia::reset();
});
it('submits and polls a Gladia pre-recorded transcription', function (): void {
    $client = new class implements ClientInterface {
        public array $requests = [];
        private array $responses;
        public function __construct()
        {
            $this->responses = [
                ['id' => 'tr_1', 'status' => 'queued'],
                ['id' => 'tr_1', 'status' => 'done', 'result' => ['metadata' => ['audio_duration' => 1.2], 'transcription' => ['full_transcript' => 'Hello world', 'languages' => ['en'], 'utterances' => [['text' => 'Hello world', 'start' => 0, 'end' => 1.2, 'speaker' => 0]]]]],
            ];
        } public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->requests[] = $request;
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(array_shift($this->responses)));
        }
    };
    $factory = new Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
    Gladia::create(['apiKey' => 'assembly-test', 'pollingIntervalMs' => 0]);
    $result = Generate::transcription()->model(Gladia::model('solaria-3'))->audio(Content::audio('https://example.com/audio.mp3'))->run();
    expect($result->output->text)->toBe('Hello world')->and($result->output->segments[0]->end)->toBe(1.2)->and((string) $client->requests[0]->getUri())->toBe('https://api.gladia.io/v2/pre-recorded')->and((string) $client->requests[1]->getUri())->toBe('https://api.gladia.io/v2/pre-recorded/tr_1')->and($client->requests[0]->getHeaderLine('x-gladia-key'))->toBe('assembly-test');
});
