<?php

namespace App\Http\Resources\V1;

use App\Models\Build;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildApiResource extends JsonResource
{
    /**
     * @var int
     */
    private $applicationActiveDevices;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  int $applicationActiveDevices
     * @return void
     */
    public function __construct($resource, int $applicationActiveDevices)
    {
        $this->resource = $resource;
        $this->applicationActiveDevices = $applicationActiveDevices;
    }
    
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->resource->map(function (Collection $collection) {
            return $collection->map(function (Build $build) {

                $buildData = $build->toArray();
                if (! $build->partial_rollout && $build->rollout_percentage !== 100) {
                    $buildData['rollout_percentage'] = 100;
                }
                $buildData['installations_percent'] = $this->applicationActiveDevices > 0 ?
                    (int) floor(($buildData['installations'] / $this->applicationActiveDevices) * 100) :
                    0;

                return $buildData;
            });
        });

        return ['data' => $data];
    }
}
