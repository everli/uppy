<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class ApplicationRepository
{
    /**
     * Create a new application.
     *
     * @param array $attributes
     *
     * @return Application
     */
    public function create(array $attributes): Application
    {
        /** @var UploadedFile $iconFile */
        $iconFile = Arr::get($attributes, 'icon');
        $iconPath = $iconFile->storeAs(
            Arr::get($attributes, 'slug'),
            'icon.' . $iconFile->getClientOriginalExtension(),
            ['disk' => config('filesystems.cloud')]
        );

        return Application::create([
            'name' => Arr::get($attributes, 'name'),
            'slug' => Arr::get($attributes, 'slug'),
            'description' => Arr::get($attributes, 'description'),
            'icon' => $iconPath,
        ]);
    }
}
