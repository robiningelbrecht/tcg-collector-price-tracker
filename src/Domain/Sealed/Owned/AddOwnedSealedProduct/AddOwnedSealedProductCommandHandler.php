<?php

declare(strict_types=1);

namespace App\Domain\Sealed\Owned\AddOwnedSealedProduct;

use App\Domain\Sealed\Owned\OwnedSealedProduct;
use App\Domain\Sealed\Owned\OwnedSealedProductRepository;
use App\Domain\Sealed\SealedProductRepository;
use App\Infrastructure\CQRS\Command\Command;
use App\Infrastructure\CQRS\Command\CommandHandler;
use App\Infrastructure\CQRS\Command\CouldNotProcessCommand;

final readonly class AddOwnedSealedProductCommandHandler implements CommandHandler
{
    public function __construct(
        private SealedProductRepository $sealedProductRepository,
        private OwnedSealedProductRepository $ownedSealedProductRepository,
    ) {
    }

    public function handle(Command $command): void
    {
        assert($command instanceof AddOwnedSealedProduct);

        $sealedProduct = $this->sealedProductRepository->find($command->getProductId());
        if (null === $sealedProduct) {
            throw CouldNotProcessCommand::withReason(sprintf('Product %d is not known.', $command->getProductId()));
        }

        if (null !== $this->ownedSealedProductRepository->find($command->getProductId())) {
            throw CouldNotProcessCommand::withReason(sprintf('%s is already in your collection.', $sealedProduct->getName()));
        }

        $this->ownedSealedProductRepository->save(OwnedSealedProduct::create(
            productId: $command->getProductId(),
            quantity: $command->getQuantity(),
            paidPrice: $command->getPaidPrice(),
            addedOn: new \DateTimeImmutable(),
        ));
    }
}
