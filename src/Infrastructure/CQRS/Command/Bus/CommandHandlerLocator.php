<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Command\Bus;

use App\Infrastructure\CQRS\CanNotRegisterCQRSHandler;
use App\Infrastructure\CQRS\Command\CommandHandler;

final readonly class CommandHandlerLocator
{
    private const string HANDLER_SUFFIX = 'CommandHandler';

    /**
     * @param iterable<CommandHandler> $handlers
     *
     * @return array<string, non-empty-list<\Closure>>
     */
    public static function fromCallables(iterable $handlers): array
    {
        $registeredHandlers = [];

        foreach ($handlers as $handler) {
            if (!str_ends_with($handler::class, self::HANDLER_SUFFIX)) {
                throw new CanNotRegisterCQRSHandler(sprintf('Fqcn "%s" does not end with "%s"', $handler::class, self::HANDLER_SUFFIX));
            }

            $commandFqcn = substr($handler::class, 0, -strlen(self::HANDLER_SUFFIX));
            if (!class_exists($commandFqcn)) {
                throw new CanNotRegisterCQRSHandler(sprintf('No corresponding command for %s "%s" found. Expected namespace: %s', self::HANDLER_SUFFIX, $handler::class, $commandFqcn));
            }
            if (str_ends_with($commandFqcn, 'Command')) {
                throw new CanNotRegisterCQRSHandler('Command name cannot end with "Command"');
            }

            $registeredHandlers[$commandFqcn][] = $handler->handle(...);
        }

        return $registeredHandlers;
    }
}
