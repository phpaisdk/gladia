<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\Contracts\Model;
use AiSdk\Gladia\GladiaOptions;
use AiSdk\Gladia\GladiaProvider;

final class Gladia
{
    private static ?GladiaProvider $default = null;
    /** @param array<string,mixed> $config */ public static function create(array $config = []): GladiaProvider
    {
        return self::$default = new GladiaProvider(GladiaOptions::fromArray($config));
    } public static function default(): GladiaProvider
    {
        return self::$default ??= self::create();
    } public static function reset(): void
    {
        self::$default = null;
    } public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
