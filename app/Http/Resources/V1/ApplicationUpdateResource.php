<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationUpdateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'forced' => false, // the endpoint v1 is deprecated
            'version' => $this->version,
            'download_url' => url('/applications/'.$this->application->slug.'/'.$this->platform),
        ];
    }
}
