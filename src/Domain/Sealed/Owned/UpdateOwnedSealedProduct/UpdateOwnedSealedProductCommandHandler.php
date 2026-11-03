<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Owned\UpdateOwnedSealedProduct;

use App\Domain\Sealed\Owned\OwnedSealedProductRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class UpdateOwnedSealedProductCommandHandler implements CommandHandler
{
    public function __construct(
        private OwnedSealedProductRepository $ownedSealedProductRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof UpdateOwnedSealedProduct);

        $ownedSealedProduct = $this->ownedSealedProductRepository->find($command->getProductId());
        if (null === $ownedSealedProduct) {
            throw CouldNotProcessCommand::withReason('This product is not in your collection.');
        }

        $this->ownedSealedProductRepository->save($ownedSealedProduct
            ->withQuantity($command->getQuantity())
            ->withPaidPrice($command->getPaidPrice()));
    }
}
