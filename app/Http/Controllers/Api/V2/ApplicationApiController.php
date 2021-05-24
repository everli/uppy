<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;


use App\Events\UpdateCheck;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\UpdateRequest;
use App\Http\Resources\V2\ApplicationUpdateResource;
use App\Models\Application;
use App\Platforms\Platform;
use Illuminate\Http\Response;

class ApplicationApiController extends Controller
{

    /**
     * @param  UpdateRequest  $request
     * @param  Application  $application
     * @param  Platform  $platform
     * @return ApplicationUpdateResource|\Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function updates(UpdateRequest $request, Application $application, Platform $platform)
    {
        $currentBuild = $this->builds->getByVersion($application, $platform, $request->get('version'));
        $deviceId = $request->get('device_id');
        $before = now();

        event(new UpdateCheck($deviceId, $application, $currentBuild));

        do {
            $newBuild = $this->builds->getUpdate($application, $platform, $request->get('version'), $before);
            $buildFound = true;

            if (
                $newBuild !== null &&
                $newBuild->partial_rollout &&
                $deviceId !== null &&
                !$this->builds->isDeviceInRolloutRange($newBuild, $deviceId)
            ) {
                $before = $newBuild->available_from;
                $buildFound = false;
            }

        } while (!$buildFound);

        if ($newBuild === null) {
            return response()->json([
                'message' => 'No available update.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new ApplicationUpdateResource($newBuild);
    }

}
