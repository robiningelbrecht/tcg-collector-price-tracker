<?php

declare(strict_types=1);

namespace App\Infrastructure\DependencyInjection;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

final readonly class SqliteDatabaseUrlEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): string
    {
        $directory = rtrim((string) $getEnv($name), '/');

        return 'sqlite:///'.$directory.'/tcg-collector.db';
    }

    public static function getProvidedTypes(): array
    {
        return [
            'sqlite_db_url' => 'string',
        ];
    }
}
