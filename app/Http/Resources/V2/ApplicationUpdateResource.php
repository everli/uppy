<?php
declare(strict_types=1);

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationUpdateResource extends JsonResource
{
    /**
     * @var bool
     */
    private $forced = false;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'forced' => $this->forced,
            'version' => $this->version,
            'download_url' => url(sprintf("/applications/%s/%s", $this->application->slug, $this->platform)),
        ];
    }

    /**
     * @param  bool  $flag
     * @return $this
     */
    public function withForcedFlag(bool $flag): ApplicationUpdateResource
    {
        $this->forced = $flag;
        return $this;
    }
}
