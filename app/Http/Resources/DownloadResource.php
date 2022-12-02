<?php

namespace App\Http\Resources;

use App\Models\Build;
use Illuminate\Http\Resources\Json\JsonResource;

class DownloadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Build $this */
        return [
            'name' => $this->application->name,
            'icon' => route('applications.icon', $this->application),
            'version' => $this->version,
            'date' => $this->available_from->toDateString(),
            'download_url' => route('applications.install', [
                $this->application->slug,
                $this->platform,
                $this->id,
            ]),
            'changelogs' => $this->changelogs,
            'organization' => config('uppy.organization')
        ];
    }
}
