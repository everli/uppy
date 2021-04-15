<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationEventsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->map(function ($build) {
            return [
                'platform' => $build->platform,
                'version' => $build->version,
                'downloads' => $build->downloads,
            ];
        })->groupBy('platform');
    }
}
