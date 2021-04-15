<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Build
 * @package App\Models
 *
 * @property int $application_id
 * @property Application $application
 * @property string $platform
 * @property string $version
 * @property string $file
 * @property bool $forced
 * @property Carbon $available_from
 * @property string plist_url
 *
 */
class Build extends Model
{
    protected $appends = ['downloads', 'installations'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'forced' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'available_from',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'platform',
        'version',
        'file',
        'forced',
        'available_from',
    ];

    /**
     * Get the application of the build.
     */
    public function application()
    {
        return $this->belongsTo(Application::class)->withDefault();
    }

    /**
     * Returns all the events for the build.
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(BuildEvent::class);
    }

    /**
     * All the device with this build installed
     *
     * @return HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * All the changelogs related to this build
     *
     * @return HasMany
     */
    public function changelogs()
    {
        return $this->hasMany(Changelog::class);
    }

    /**
     * Returns the number of downloads for the build.
     *
     * @return int
     */
    public function getDownloadsAttribute(): int
    {
        return $this->events()->where('event', 'download')->count();
    }

    /**
     * Returns the last tracked installations
     *
     * @return int
     */
    public function getInstallationsAttribute(): int
    {
        return $this->devices()->count();
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
