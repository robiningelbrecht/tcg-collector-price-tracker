<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Domain\Binder\Binder;
use League\Flysystem\FilesystemOperator;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Component\DependencyInjection\Attribute\Target;

final readonly class BinderPhotoStorage
{
    private const array EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    public function __construct(
        #[Target('binder.photos.storage')]
        private FilesystemOperator $storage,
    ) {
    }

    public static function supports(string $mimeType): bool
    {
        return isset(self::EXTENSIONS[$mimeType]);
    }

    public static function detectMimeType(string $contents): string
    {
        return new FinfoMimeTypeDetector()->detectMimeTypeFromBuffer($contents) ?? 'application/octet-stream';
    }

    public function store(Binder $binder, int $pageNumber, string $contents): string
    {
        $mimeType = self::detectMimeType($contents);
        if (!self::supports($mimeType)) {
            throw new \InvalidArgumentException(sprintf('Unsupported photo type %s', $mimeType));
        }

        $fileName = sprintf('page-%d-%s.%s', $pageNumber, bin2hex(random_bytes(6)), self::EXTENSIONS[$mimeType]);
        $this->storage->write($this->location($binder, $fileName), $contents);

        return $fileName;
    }

    public function exists(Binder $binder, string $fileName): bool
    {
        return $this->storage->fileExists($this->location($binder, $fileName));
    }

    public function read(Binder $binder, string $fileName): string
    {
        return $this->storage->read($this->location($binder, $fileName));
    }

    public function mimeType(Binder $binder, string $fileName): string
    {
        return $this->storage->mimeType($this->location($binder, $fileName));
    }

    public function delete(Binder $binder, string $fileName): void
    {
        $this->storage->delete($this->location($binder, $fileName));
    }

    public function deleteAll(Binder $binder): void
    {
        $this->storage->deleteDirectory($this->directory($binder));
    }

    private function location(Binder $binder, string $fileName): string
    {
        return $this->directory($binder).'/'.basename($fileName);
    }

    private function directory(Binder $binder): string
    {
        return $binder->getLanguage()->value.'/'.basename($binder->getCardSetId());
    }
}
