<?php
namespace App\Services;
class UploadService
{
    private string $uploadDir;
    private string $projectRoot;
    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/\\');
        $this->projectRoot = dirname($this->uploadDir);
    }
    public function store(?array $file, string $folder = 'common'): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        if (!in_array($extension, $allowed, true)) {
            throw new \RuntimeException('Допустимы изображения: jpg, png, webp, gif, svg.');
        }
        $targetDir = $this->uploadDir . DIRECTORY_SEPARATOR . trim($folder, '/\\');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Не удалось сохранить загруженный файл.');
        }
        $relativePath = 'uploads/' . trim($folder, '/\\') . '/' . $filename;
        if ($folder === 'avatars') {
            $this->ensureAvatarVariant($relativePath, 84);
            $this->ensureAvatarVariant($relativePath, 168);
        }
        return $relativePath;
    }
    public function avatarSources(?string $path, int $baseSize = 84, int $retinaSize = 168): array
    {
        $fallback = \upload_url($path);
        if (!$path || preg_match('#^https?://#i', $path)) {
            return [
                'src' => $fallback,
                'srcset' => '',
            ];
        }
        $base = $this->ensureAvatarVariant($path, $baseSize) ?? $path;
        $retina = $this->ensureAvatarVariant($path, $retinaSize) ?? $base;
        return [
            'src' => \upload_url($base),
            'srcset' => $retina !== $base
                ? \upload_url($base) . ' 1x, ' . \upload_url($retina) . ' 2x'
                : '',
        ];
    }
    private function ensureAvatarVariant(string $relativePath, int $size): ?string
    {
        $sourcePath = $this->resolvePath($relativePath);
        if ($sourcePath === null || !is_file($sourcePath)) {
            return null;
        }
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if ($extension === 'svg') {
            return null;
        }
        $targetExtension = $this->preferredExtension($extension);
        if ($targetExtension === null) {
            return null;
        }
        $relativeDir = trim(str_replace('\\', '/', dirname($relativePath)), '/.');
        $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
        $variantDir = ($relativeDir !== '' ? $relativeDir . '/' : '') . 'optimized';
        $variantRelativePath = $variantDir . '/' . $baseName . '_' . $size . '.' . $targetExtension;
        $variantPath = $this->resolvePath($variantRelativePath);
        if ($variantPath === null) {
            return null;
        }
        if (is_file($variantPath) && filemtime($variantPath) >= filemtime($sourcePath)) {
            return $variantRelativePath;
        }
        if (!is_dir(dirname($variantPath))) {
            mkdir(dirname($variantPath), 0775, true);
        }
        if (class_exists(\Imagick::class)) {
            if ($this->createVariantWithImagick($sourcePath, $variantPath, $size, $targetExtension)) {
                return $variantRelativePath;
            }
        }
        if ($this->createVariantWithGd($sourcePath, $variantPath, $size, $targetExtension)) {
            return $variantRelativePath;
        }
        return null;
    }
    private function resolvePath(string $relativePath): ?string
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }
        return $this->projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
    private function preferredExtension(string $sourceExtension): ?string
    {
        if (function_exists('imagewebp')) {
            return 'webp';
        }
        return match ($sourceExtension) {
            'jpg', 'jpeg' => function_exists('imagejpeg') ? 'jpg' : null,
            'png', 'gif', 'webp' => function_exists('imagepng') ? 'png' : null,
            default => null,
        };
    }
    private function createVariantWithImagick(string $sourcePath, string $targetPath, int $size, string $extension): bool
    {
        try {
            $image = new \Imagick($sourcePath);
            $image->setImageOrientation(\Imagick::ORIENTATION_UNDEFINED);
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
            if ($width <= 0 || $height <= 0) {
                $image->clear();
                $image->destroy();
                return false;
            }
            $cropSize = min($width, $height);
            $x = (int) floor(($width - $cropSize) / 2);
            $y = (int) floor(($height - $cropSize) / 2);
            $image->cropImage($cropSize, $cropSize, $x, $y);
            $image->setImagePage(0, 0, 0, 0);
            $image->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1, true);
            if ($extension === 'webp') {
                $image->setImageFormat('webp');
                $image->setImageCompressionQuality(82);
            } elseif ($extension === 'jpg') {
                $image->setImageFormat('jpeg');
                $image->setImageCompressionQuality(84);
                $image->setImageBackgroundColor('white');
                $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            } else {
                $image->setImageFormat('png');
                $image->setOption('png:compression-level', '9');
            }
            $ok = $image->writeImage($targetPath);
            $image->clear();
            $image->destroy();
            return (bool) $ok;
        } catch (\Throwable) {
            return false;
        }
    }
    private function createVariantWithGd(string $sourcePath, string $targetPath, int $size, string $extension): bool
    {
        if (!function_exists('getimagesize')) {
            return false;
        }
        $info = @getimagesize($sourcePath);
        if (!is_array($info) || empty($info['mime'])) {
            return false;
        }
        $source = match ($info['mime']) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($sourcePath) : false,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($sourcePath) : false,
            'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($sourcePath) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };
        if (!$source) {
            return false;
        }
        $width = imagesx($source);
        $height = imagesy($source);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($source);
            return false;
        }
        $cropSize = min($width, $height);
        $srcX = (int) floor(($width - $cropSize) / 2);
        $srcY = (int) floor(($height - $cropSize) / 2);
        $canvas = imagecreatetruecolor($size, $size);
        if ($extension !== 'jpg') {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
        }
        imagecopyresampled($canvas, $source, 0, 0, $srcX, $srcY, $size, $size, $cropSize, $cropSize);
        $saved = match ($extension) {
            'webp' => function_exists('imagewebp') ? @imagewebp($canvas, $targetPath, 82) : false,
            'jpg' => function_exists('imagejpeg') ? @imagejpeg($canvas, $targetPath, 84) : false,
            'png' => function_exists('imagepng') ? @imagepng($canvas, $targetPath, 9) : false,
            default => false,
        };
        imagedestroy($canvas);
        imagedestroy($source);
        return (bool) $saved;
    }
}