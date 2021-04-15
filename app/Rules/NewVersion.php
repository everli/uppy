<?php

namespace App\Rules;

use App\Models\Application;
use App\Models\Build;
use App\Platforms\Platform;
use Composer\Semver\Comparator;
use Illuminate\Contracts\Validation\Rule;

class NewVersion implements Rule
{
    /**
     * @var Platform
     */
    private $platform;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var Build
     */
    private $lastPackage;

    /**
     * NewVersion constructor.
     *
     * @param Application $application
     * @param Platform $platform
     */
    public function __construct(Application $application, Platform $platform)
    {
        $this->application = $application;
        $this->platform = $platform;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        /** @var Build $lastPackage */
        $lastPackage = $this->application->builds()
            ->where('platform', $this->platform->getId())
            ->latest()
            ->first();

        if ($lastPackage === null) {
            return true;
        }

        if (Comparator::greaterThan($value, $lastPackage->version)) {
            return true;
        }

        $this->lastPackage = $lastPackage;

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The {$this->platform->getId()} version of {$this->application->name} must be greater than {$this->lastPackage->version}";
    }
}
