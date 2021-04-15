<?php

namespace App\Platforms;

use App\Models\Application;
use App\Models\Build;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Cloud;
use RuntimeException;

abstract class Platform
{
    // time in minutes before the temporary url expires
    const DEFAULT_LINK_EXPIRATION = 30;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $mimeTypes = [];

    /**
     * @return array
     */
    public function getMimeTypes(): array
    {
        if (empty($this->mimeTypes)) {
            throw new RuntimeException('At least one mime type must be defined');
        }

        return $this->mimeTypes;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        if ($this->id === null || $this->id === '') {
            throw new RuntimeException('The identifier cannot be empty');
        }

        return $this->id;
    }

    /**
     * Returns the download url.
     *
     * @param Application $application
     * @param Build $build
     *
     * @param Cloud $storage
     * @return mixed
     */
    abstract public function getDownloadUrl(Application $application, Build $build, Cloud $storage);

    /**
     * Returns the file url.
     *
     * @param Build $build
     * @param Cloud $storage
     * @return mixed
     */
    public function getFileUrl(Build $build, Cloud $storage): string
    {
        try {
            return $storage->temporaryUrl(
                $build->file,
                Carbon::now()->addMinutes(config('filesystems.temporary_url_expiration', self::DEFAULT_LINK_EXPIRATION))
            );
        } catch (RuntimeException $exception) {
            return url($storage->url($build->file));
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId();
    }
}
