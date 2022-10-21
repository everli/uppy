<?php
declare(strict_types=1);

namespace App\Platforms;

use App\Models\Application;
use App\Models\Build;
use Illuminate\Contracts\Filesystem\Cloud;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AndroidPlatform extends Platform
{
    /**
     * @inheritDoc
     */
    protected $id = 'Android';

    /**
     * @inheritDoc
     */
    protected $mimeTypes = [
        'application/vnd.android.package-archive',
    ];

    /**
     * @inheritDoc
     */
    public function getDownloadUrl(Application $application, Build $build, Cloud $storage): string
    {
        return $this->getFileUrl($build, $storage);
    }

    /**
     * @param  UploadedFile  $file
     * @return string|null
     */
    public function getPackage(UploadedFile $file): ?string
    {
        // TODO: Implement getPackage() method.
        return null;
    }
}
