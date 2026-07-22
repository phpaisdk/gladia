<?php

declare(strict_types=1);

namespace AiSdk\Gladia;

use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\TranscriptionModelInterface;
use AiSdk\Contracts\TranscriptionProviderInterface;
use AiSdk\Gladia\Models\GladiaTranscriptionModel;

final class GladiaProvider extends BaseProvider implements TranscriptionProviderInterface
{
    public function __construct(public readonly GladiaOptions $options) {} public function name(): string
    {
        return GladiaOptions::PROVIDER_NAME;
    } protected function transcriptionModel(string $modelId): TranscriptionModelInterface
    {
        return new GladiaTranscriptionModel($modelId, $this->options);
    }
}
