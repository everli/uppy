<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Application;
use App\Models\Build;
use App\Models\Device;
use App\Platforms\Platform;
use App\Platforms\PlatformService;
use Carbon\Carbon;
use Composer\Semver\Comparator;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BuildRepository
{
    /**
     * Create a new build.
     *
     * @param  Application  $application
     * @param  Platform  $platform
     * @param  array  $attributes
     *
     * @return Build|\Illuminate\Database\Eloquent\Model
     */
    public function create(Application $application, Platform $platform, array $attributes)
    {
        return DB::transaction(function () use ($attributes, $platform, $application) {

            /** @var UploadedFile $artifact */
            $artifact = Arr::get($attributes, 'file');
            $availableFrom = Carbon::parse(Arr::get($attributes, 'available_from'));

            $path = $this->storeArtifact($artifact, $application, $platform, Arr::get(
                $attributes,
                'version'
            ));

            /** @var Build $build */
            $build = $application->builds()->create([
                'platform' => $platform->getId(),
                'version' => Arr::get($attributes, 'version'),
                'file' => $path,
                'dismissed' => Arr::get($attributes, 'dismissed', false),
                'available_from' => $availableFrom->toDateTimeString(),
                'partial_rollout' => Arr::get($attributes, 'partial_rollout', false),
                'rollout_percentage' => Arr::get($attributes, 'rollout_percentage', 0),
            ]);

            $this->saveChangelogs($build, Arr::get($attributes, 'changelogs', []));

            return $build;
        });
    }

    /**
     * @param  Build  $build
     * @param  array  $attributes
     * @return mixed
     */
    public function update(Build $build, array $attributes)
    {
        return DB::transaction(function () use ($build, $attributes) {
            $changelogs = Arr::get($attributes, 'changelogs');

            // storing updated changelogs
            if ($changelogs !== null) {
                $build->changelogs()->delete();
                $this->saveChangelogs($build, $changelogs);
            }

            /** @var UploadedFile $artifact */
            $artifact = Arr::get($attributes, 'file');

            // if the new file is specified, store it
            if ($artifact !== null) {
                Storage::cloud()->delete($build->file);

                $path = $this->storeArtifact(
                    $artifact,
                    $build->application,
                    app(PlatformService::class)->get($build->platform),
                    $build->version
                );

                Arr::set($attributes, 'file', $path);
            } else {
                Arr::forget($attributes, 'file');
            }

            $build->update($attributes);
        });
    }

    /**
     * Get the update for the given Application and the given Platform if available.
     *
     * @param  Application  $application
     * @param  Platform  $platform
     * @param  string  $version
     * @param  Carbon|null  $before
     * @return Build
     */
    public function getUpdate(
        Application $application,
        Platform $platform,
        string $version,
        ?Carbon $before = null
    ): ?Build
    {

        /** @var Build $lastAvailableBuild */
        $lastAvailableBuild = $application->builds()
            ->where('platform', $platform->getId())
            ->where('available_from', '<', $before ?? now())
            ->latest('available_from')
            ->first();

        if ($lastAvailableBuild === null) {
            return null;
        }

        if (Comparator::greaterThan($version, $lastAvailableBuild->version)) {
            return null;
        }

        if (Comparator::equalTo($version, $lastAvailableBuild->version)) {
            return null;
        }

        return $lastAvailableBuild;
    }

    /**
     * Get last available build.
     *
     * @param  Application  $application
     * @param  Platform  $platform
     *
     * @return Build|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getLastBuild(Application $application, Platform $platform)
    {
        return $application->builds()->where('platform', $platform->getId())
            ->where('available_from', '<=', Carbon::now())
            ->latest()
            ->first();
    }

    /**
     * Return the build grouped by platform
     *
     * @param  Application  $application
     * @return Collection
     */
    public function getByPlatform(Application $application): Collection
    {
        return $application->builds()
            ->latest()
            ->get()
            ->groupBy('platform')
            ->sortKeys();
    }

    /**
     * Return a build by version
     *
     * @param  Application  $application
     * @param  Platform  $platform
     * @param  string  $version
     * @return Build|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getByVersion(Application $application, Platform $platform, string $version)
    {
        return $application->builds()
            ->where('platform', $platform->getId())
            ->where('version', $version)
            ->first();
    }

    /**
     * Remove the build from the filesystem and database
     *
     * @param  Build  $build
     * @return bool|null
     * @throws Exception
     */
    public function delete(Build $build): ?bool
    {
        Storage::cloud()->delete($build->file);
        return $build->delete();
    }

    /**
     * @param  Build  $build
     * @param  array  $changelogs
     */
    public function saveChangelogs(Build $build, array $changelogs): void
    {
        foreach ($changelogs as $locale => $content) {
            $build->changelogs()->create([
                'locale' => $locale,
                'content' => $content
            ]);
        }
    }

    /**
     * @param  UploadedFile  $buildFile
     * @param  Application  $application
     * @param  Platform  $platform
     * @param  string  $version
     * @return false|string
     */
    public function storeArtifact(
        UploadedFile $buildFile,
        Application $application,
        Platform $platform,
        string $version
    ) {
        return $buildFile->storeAs(
            $application->slug,
            sprintf("%s-%s-%s.%s",
                $application->slug,
                $platform->getId(),
                $version,
                $buildFile->getClientOriginalExtension()
            ),
            ['disk' => config('filesystems.cloud')]
        );
    }

    /**
     * @param  Build  $build
     * @param  string  $deviceId
     * @return bool
     */
    public function isDeviceInRolloutRange(Build $build, string $deviceId): bool
    {
        // only devices that contacted the backend
        // after this date are considered active
        $threshold = now()
            ->subDays(config('uppy.active_device_threshold'))
            ->toDateTimeString();

        // get all the active users for this application
        $activeDevices = Device::query()
            ->where('updated_at', '>', $threshold)
            ->where('application_id', $build->application_id)
            ->orderBy('device_id');

        // calculate how many devices should get the update notification
        // according to the rollout percentage
        $deviceCount = $activeDevices->count();
        $devicesInRange = ($build->rollout_percentage / 100) * $deviceCount;

        // if there are no device range to be considered, return false
        if ($devicesInRange === 0) {
            return false;
        }

        // define a range:
        // the first device in range is the first by device_id
        // the last device instead is the last one counting n devices
        // in the range
        $firstInRange = $activeDevices->first();

        // using the same query, filtering the devices and sorting by device id,
        // we find the last device according to the rollout percentage
        $lastInRange = (clone $activeDevices)
            ->offset($devicesInRange - 1)
            ->first();

        if ($firstInRange === null || $lastInRange === null) {
            return false;
        }

        // select all devices between the first one and the last one (so the full
        // partial rollout range), if in that range the current device_id is included,
        // it can receive the update
        return $activeDevices
            ->whereBetween('device_id', [$firstInRange->device_id, $lastInRange->device_id])
            ->where('device_id', $deviceId)
            ->exists();
    }
}
