<?php
declare(strict_types=1);

namespace App\Platforms;

use App\Models\Application;
use App\Models\Build;
use Illuminate\Contracts\Filesystem\Cloud;

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
}
