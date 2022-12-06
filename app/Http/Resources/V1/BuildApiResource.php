<?php

namespace App\Http\Resources\V1;

use App\Models\Build;
use App\Platforms\Platform;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jenssegers\Agent\Agent;

class BuildApiResource extends JsonResource
{
    /**
     * @var int
     */
    private $applicationActiveDevices;

    /**
     * @var Agent|null
     */
    private $agent;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param int $applicationActiveDevices
     * @param Agent|null $agent
     */
    public function __construct($resource, int $applicationActiveDevices, ?Agent $agent = null)
    {
        parent::__construct($resource);
        $this->resource = $resource;
        $this->applicationActiveDevices = $applicationActiveDevices;
        $this->agent = $agent;
    }
    
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
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
                $buildData['download_url'] = $this->getDownloadUrl($build);

                return $buildData;
            });
        });

        return ['data' => $data];
    }

    /**
     * @param Build $build
     * @return string
     */
    private function getDownloadUrl(Build $build): string
    {
        return route('applications.raw', [
            $build->application->slug,
            $build->platform,
            $build->id,
        ]);
    }
}
