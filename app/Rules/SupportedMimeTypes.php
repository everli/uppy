<?php

namespace App\Rules;

use App\Platforms\Platform;
use App\Platforms\PlatformService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class SupportedMimeTypes implements Rule
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
     * @param string $attribute
     * @param UploadedFile $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$value instanceof UploadedFile) {
            return false;
        }

        foreach (app(PlatformService::class)->getSupportedPlatforms() as $platformClass) {
            /** @var Platform $platform */
            $platform = new $platformClass();

            if (in_array($value->getClientMimeType(), $platform->getMimeTypes())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a supported mime type.';
    }
}
