<?php

declare(strict_types=1);

namespace App\Platforms;

use App\Models\Application;
use App\Models\Build;
use Illuminate\Contracts\Filesystem\Cloud;

class IOSPlatform extends Platform
{
    /**
     * @inheritDoc
     */
    protected $id = 'iOS';

    /**
     * @inheritDoc
     */
    protected $mimeTypes = [
        'application/octet-stream',
    ];

    /**
     * @inheritDoc
     */
    public function getDownloadUrl(Application $application, Build $build, Cloud $storage)
    {
        $plistUrl = route('applications.plist', [$application->slug, $this->getId()]);
        return 'itms-services://?action=download-manifest&url='.urlencode($plistUrl);
    }
}
