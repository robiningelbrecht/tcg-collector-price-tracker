<?php

declare(strict_types=1);

namespace App\Domain\Binder\Page;

use App\Domain\Binder\Binder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

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

    public static function detectMimeTypeOfContents(string $contents): string
    {
        return (string) new \finfo(FILEINFO_MIME_TYPE)->buffer($contents);
    }

    public function store(Binder $binder, int $pageNumber, string $contents): string
    {
        $mimeType = self::detectMimeTypeOfContents($contents);
        if (!self::supports($mimeType)) {
            throw new \InvalidArgumentException(sprintf('Unsupported photo type %s', $mimeType));
        }

        $directory = $this->binderDirectory($binder);
        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Could not create directory %s', $directory));
        }

        $fileName = sprintf('page-%d-%s.%s', $pageNumber, bin2hex(random_bytes(6)), self::EXTENSIONS[$mimeType]);
        if (false === file_put_contents($directory.'/'.$fileName, $contents)) {
            throw new \RuntimeException(sprintf('Could not write photo %s', $fileName));
        }

        return $fileName;
    }

    public function delete(Binder $binder, string $fileName): void
    {
        $path = $this->path($binder, $fileName);
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function deleteAll(Binder $binder): void
    {
        $directory = $this->binderDirectory($binder);
        if (!is_dir($directory)) {
            return;
        }

        foreach (glob($directory.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($directory);
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
