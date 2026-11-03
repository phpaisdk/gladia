# PHP AI SDK — Gladia

Audio transcription with Gladia for the framework-agnostic [PHP AI SDK](https://phpaisdk.com).

## Installation

```bash
composer require aisdk/gladia
```

Set `GLADIA_API_KEY` in your environment.

## Usage

```php
use AiSdk\Content;
use AiSdk\Generate;
use AiSdk\Gladia;

$result = Generate::transcription(Content::audio(__DIR__.'/meeting.mp3'))
    ->model(Gladia::model('solaria-1'))
    ->run();

echo $result->output->text;
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/gladia) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
