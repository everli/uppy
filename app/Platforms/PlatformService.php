<?php

declare(strict_types=1);

namespace App\Platforms;


use App\Exceptions\PlatformNotFoundException;
use Generator;
use Illuminate\Http\UploadedFile;
use Jenssegers\Agent\Agent;
use RuntimeException;

class PlatformService
{
    /**
     * @var array
     */
    protected $supportedPlatforms;

    /**
     * PlatformProxy constructor.
     * @param array $supportedPlatforms
     */
    public function __construct(array $supportedPlatforms)
    {
        if (empty($supportedPlatforms)) {
            throw new RuntimeException('You must support at least one platform.');
        }

        $this->supportedPlatforms = $supportedPlatforms;
    }

    /**
     * Get the platform with a given id.
     *
     * @param string $id
     *
     * @return Platform
     */
    public function get(string $id): Platform
    {
        foreach ($this->iterateSupportedPlatforms() as $platform) {
            if ($platform->getId() === $id) {
                return $platform;
            }
        }

        throw new PlatformNotFoundException();
    }

    /**
     * Guess the platform starting from the uploaded file.
     *
     * @param UploadedFile $build
     *
     * @return Platform
     */
    public function guessFromFile(UploadedFile $build): Platform
    {
        foreach ($this->iterateSupportedPlatforms() as $platform) {
            if (in_array($build->getClientMimeType(), $platform->getMimeTypes())) {
                return $platform;
            }
        }

        throw new PlatformNotFoundException();
    }

    /**
     * Try to match the platform with a user agent
     * @param Agent $agent
     * @return Platform
     */
    public function matchFromAgent(Agent $agent): Platform
    {
        foreach ($this->iterateSupportedPlatforms() as $platform) {
            if ($agent->is($platform->getId())) {
                return $platform;
            }
        }

        $platform = config('uppy.default_platform');
        return new $platform();
    }

    /**
     * Iterator over supported platforms
     * @return Generator|null
     */
    protected function iterateSupportedPlatforms(): ?Generator
    {
        foreach ($this->supportedPlatforms as $platformClass) {
            yield new $platformClass();
        }
    }

    /**
     * @return array
     */
    public function getSupportedPlatforms(): array
    {
        return $this->supportedPlatforms;
    }
}
