<?php

namespace App\Rules;

use Composer\Semver\VersionParser;
use Illuminate\Contracts\Validation\Rule;
use UnexpectedValueException;

class SemVer implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            $version = new VersionParser();
            $version->normalize($value);

            return true;
        } catch (UnexpectedValueException $exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid version string.';
    }
}
