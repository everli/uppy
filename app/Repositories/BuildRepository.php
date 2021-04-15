<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Application;
use App\Models\Build;
use App\Platforms\Platform;
use Carbon\Carbon;
use Composer\Semver\Comparator;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
        /** @var UploadedFile $buildFile */
        $buildFile = Arr::get($attributes, 'file');
        $availableFrom = Carbon::parse(Arr::get($attributes, 'available_from'));

        return DB::transaction(function () use ($availableFrom, $attributes, $platform, $application, $buildFile) {
            $buildPath = $buildFile->storeAs(
                $application->slug,
                $application->slug.'-'.$platform->getId().'-'.Arr::get(
                    $attributes,
                    'version'
                ).'.'.$buildFile->getClientOriginalExtension(),
                ['disk' => config('filesystems.cloud')]
            );

            /** @var Build $build */
            $build = $application->builds()->create([
                'platform' => $platform->getId(),
                'version' => Arr::get($attributes, 'version'),
                'file' => $buildPath,
                'forced' => Arr::get($attributes, 'forced', 'false') === 'true',
                'available_from' => $availableFrom->toDateTimeString(),
            ]);

            foreach (Arr::get($attributes, 'changelogs', []) as $locale => $content) {
                $build->changelogs()->create([
                    'locale' => $locale,
                    'content' => $content
                ]);
            }

            return $build;
        });
    }

    /**
     * Get the update for the given Application and the given Platform if available.
     *
     * @param  Application  $application
     * @param  Platform  $platform
     * @param  string  $version
     *
     * @return Build
     */
    public function getUpdate(Application $application, Platform $platform, string $version): ?Build
    {
        // get the installed version for the platform.
        $installedVersion = $this->getByVersion($application, $platform, $version);

        $lastAvailableBuilds = $application->builds()->where('platform', $platform->getId())
            ->where('available_from', '<=', Carbon::now())
            ->when($installedVersion, function (Builder $query) use ($installedVersion) {
                // if the installed version is on DB we get all the next versions.
                $query->where('id', '>', $installedVersion->id);
            }, function (Builder $query) {
                $query->limit(1);
            })
            ->latest()
            ->get();

        if ($lastAvailableBuilds->isEmpty()) {
            return null;
        }

        /** @var Build $lastAvailableBuild */
        $lastAvailableBuild = $lastAvailableBuilds->first();

        if (Comparator::greaterThan($version, $lastAvailableBuild->version)) {
            return null;
        }

        if (Comparator::equalTo($version, $lastAvailableBuild->version)) {
            return null;
        }

        // check if there is at least a `forced` version between the installed version and the latest
        // version available, in this case we wants to set latest version available as `forced`.
        if ($lastAvailableBuilds->contains('forced', '=', true)) {
            $lastAvailableBuild->forced = true;
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
    public function getLast(Application $application, Platform $platform)
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
}
