<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Domain\Binder\Binder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        #[Autowire('%env(BINDER_PHOTO_DIRECTORY)%')]
        private string $directory,
    ) {
    }

    public static function supports(string $mimeType): bool
    {
        return isset(self::EXTENSIONS[$mimeType]);
    }

    public static function detectMimeType(string $path): string
    {
        return (string) new \finfo(FILEINFO_MIME_TYPE)->file($path);
    }

    public function store(Binder $binder, int $pageNumber, UploadedFile $file): string
    {
        $mimeType = self::detectMimeType($file->getPathname());
        if (!self::supports($mimeType)) {
            throw new \InvalidArgumentException(sprintf('Unsupported photo type %s', $mimeType));
        }

        $fileName = sprintf('page-%d-%s.%s', $pageNumber, bin2hex(random_bytes(6)), self::EXTENSIONS[$mimeType]);
        $file->move($this->binderDirectory($binder), $fileName);

        return $fileName;
    }

    public function delete(Binder $binder, string $fileName): void
    {
        $path = $this->path($binder, $fileName);
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function path(Binder $binder, string $fileName): string
    {
        return $this->binderDirectory($binder).'/'.basename($fileName);
    }

    private function binderDirectory(Binder $binder): string
    {
        return sprintf('%s/%s/%s', $this->directory, $binder->getLanguage()->value, basename($binder->getCardSetId()));
    }
}
