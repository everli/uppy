<?php
declare(strict_types=1);


return [

    /*
    |--------------------------------------------------------------------------
    | Application name
    |--------------------------------------------------------------------------
    |
    | The name of the application you are gonna distribute.
    | This will be shown, for instance, in the download page.
    |
    */
    'organization' => env('ORG_NAME', 'Uppy'),

    /*
    |--------------------------------------------------------------------------
    | Main color
    |--------------------------------------------------------------------------
    |
    | The main color of y
    |
    */
    'color' => '#FFFFFF',

    /*
    |--------------------------------------------------------------------------
    | Supported platforms
    |--------------------------------------------------------------------------
    |
    | This is the array of the supported platforms.
    | You can add new platforms simply creating a new class inside the Platforms
    | folder and define the require parameters (mime type, etc...)
    |
    */

    'platforms' => [
        \App\Platforms\IOSPlatform::class,
        \App\Platforms\AndroidPlatform::class,
    ],

    'default_platform' => \App\Platforms\AndroidPlatform::class,

    'active_device_threshold' => env('ACTIVE_DEVICE_THRESHOLD', 30), // days

];
