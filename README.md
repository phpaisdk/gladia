# aisdk/gladia

<a href="https://github.com/phpaisdk/gladia/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/gladia/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/gladia"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/gladia"></a>
<a href="https://packagist.org/packages/aisdk/gladia"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/gladia"></a>

------

Official Gladia provider for the PHP AI SDK. Uses Gladia's native pre-recorded transcription API.

## Installation

```bash
composer require aisdk/gladia
```

## Basic Usage

```php
use AiSdk\Content;
use AiSdk\Generate;
use AiSdk\Gladia;

Gladia::create(['apiKey' => $_ENV['GLADIA_API_KEY']]);

$result = Generate::transcription()
    ->model(Gladia::model('solaria-3'))
    ->audio(Content::audio('https://example.com/audio.mp3'))
    ->run();
```

## Configuration

| Variable | Description | Default |
| --- | --- | --- |
| `GLADIA_API_KEY` | API key for authentication | Required |
| `GLADIA_BASE_URL` | Base URL for API requests | `https://api.gladia.io` |

## Supported Capabilities

| Capability | Support |
| --- | --- |
| Pre-recorded transcription | Native `/v2/pre-recorded` job |
| Remote audio URLs | Native |
| Local audio files | Uploaded with `/v2/upload` |

Use `providerOptions('gladia', [...])` for Gladia request fields including diarization, language configuration, and audio-intelligence options.

## Documentation

- [Gladia pre-recorded API reference](https://docs.gladia.io/api-reference/v2/pre-recorded/init)
- [PHP AI SDK documentation](https://phpaisdk.com/docs/gladia)
