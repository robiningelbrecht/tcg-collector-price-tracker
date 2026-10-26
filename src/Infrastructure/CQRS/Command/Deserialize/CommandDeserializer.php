<?php

declare(strict_types=1);

namespace App\Infrastructure\CQRS\Command\Deserialize;

final readonly class CommandDeserializer
{
    public function __construct(
        private DeserializableCommandRegistry $registry,
    ) {
    }

    public function deserialize(string $json): DeserializableCommand
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw CouldNotDeserializeCommand::invalidPayload('Expected a valid JSON body.');
        }

        if (!is_array($decoded)
            || !isset($decoded['commandName'])
            || !is_string($decoded['commandName'])
            || !isset($decoded['payload'])
            || !is_array($decoded['payload'])) {
            throw CouldNotDeserializeCommand::invalidPayload('Expected a JSON object with a "commandName" and "payload".');
        }

        $commandClass = $this->registry->resolve($decoded['commandName']);

        /** @var array<string, mixed> $payload */
        $payload = $decoded['payload'];

        return $commandClass::fromPayload($payload);
    }
}
