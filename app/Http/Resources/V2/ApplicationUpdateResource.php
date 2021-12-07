<?php
declare(strict_types=1);
namespace App\Http\Resources\V2;

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
            'version' => $this->version,
            'download_url' => url('/applications/' . $this->application->slug . '/' . $this->platform),
        ];
    }
}
