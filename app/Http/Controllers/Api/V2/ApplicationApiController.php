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
        $before = now(); // first, we are using the current timestamp to find all builds available from this date

        event(new UpdateCheck($deviceId, $application, $currentBuild));

        do {
            $newBuild = $this->builds->getUpdate($application, $platform, $request->get('version'), $before);
            $buildFound = true; // we should have taken the latest build available

            // if the last build is in partial rollout mode, and the current
            // device is not in the rollout range, we try to retrieve the
            // previous build to check it
            if (
                $newBuild !== null &&
                $newBuild->partial_rollout &&
                $deviceId !== null &&
                !$this->builds->isDeviceInRolloutRange($newBuild, $deviceId)
            ) {
                $before = $newBuild->available_from; // moving the current available_from back in time
                $buildFound = false; // continue to iterate
            }

        } while (!$buildFound);

        // if at this point, we have not been able to find a new build,
        // it means that we already have the latest available,
        // or there are no previous builds (if newer one are in partial rollout)
        if ($newBuild === null) {
            return response()->json([
                'message' => 'No available update.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new ApplicationUpdateResource($newBuild);
    }

}
