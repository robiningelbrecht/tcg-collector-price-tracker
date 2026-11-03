<?php

declare(strict_types=1);

namespace App\Domain\Sealed\RemoveOwnedSealedProduct;

use App\Domain\Sealed\OwnedSealedProductRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class RemoveOwnedSealedProductCommandHandler implements CommandHandler
{
    public function __construct(
        private OwnedSealedProductRepository $ownedSealedProductRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof RemoveOwnedSealedProduct);

        $ownedSealedProduct = $this->ownedSealedProductRepository->find($command->getProductId());
        if (null === $ownedSealedProduct) {
            throw CouldNotProcessCommand::withReason('This product is not in your collection.');
        }

        $this->ownedSealedProductRepository->delete($ownedSealedProduct);
    }
}
