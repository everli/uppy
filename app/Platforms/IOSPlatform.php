<?php

declare(strict_types=1);

namespace App\Platforms;

use App\Models\Application;
use App\Models\Build;
use CFPropertyList\CFPropertyList;
use CFPropertyList\IOException;
use CFPropertyList\PListException;
use Illuminate\Contracts\Filesystem\Cloud;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ZipArchive;

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
        $plistUrl = route('applications.plist', [
            'application' => $application->slug,
            'platform' => $this->getId(),
            'build' => $build->plist_url
        ]);
        return 'itms-services://?action=download-manifest&url='.urlencode($plistUrl);
    }

    /**
     * @param  UploadedFile  $file
     * @return string|null
     */
    public function getPackage(UploadedFile $file): ?string
    {
        $archive = new ZipArchive();
        $archive->open($file->getRealPath());
        $content = null;

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $entry = $archive->statIndex($i);
            if (fnmatch('*/*.app/Info.plist', $entry['name'])) {
                $content = file_get_contents(sprintf("zip://%s#%s", $file->getRealPath(), $entry['name']));
                break;
            }
        }

        $plist = new CFPropertyList();
        try {
            $plist->parse($content);
        } catch (IOException|PListException|\DOMException $e) {
            return null;
        }

        return $plist->toArray()['CFBundleIdentifier'] ?? null;
    }
}
