# Gladia provider for PHP AI SDK

```bash
composer require aisdk/gladia
```

```php
use AiSdk\Gladia;
use AiSdk\Content;
use AiSdk\Generate;

Gladia::create(['apiKey' => $_ENV['GLADIA_API_KEY']]);

$result = Generate::transcription()
    ->model(Gladia::model('solaria-3'))
    ->audio(Content::audio('https://example.com/audio.mp3'))
    ->run();
```

The provider uploads local audio with Gladia's `/v2/upload` endpoint when needed, creates a `/v2/pre-recorded` job, and polls until it is done. Provider options map directly to Gladia's native pre-recorded request fields, including diarization, language configuration, and audio-intelligence options. See [Gladia's API reference](https://docs.gladia.io/api-reference/v2/pre-recorded/init).
