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
     * @param UpdateRequest $request
     * @param Application $application
     * @param Platform $platform
     * @return ApplicationUpdateResource|\Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function updates(UpdateRequest $request, Application $application, Platform $platform)
    {
        $newBuild = $this->builds->getUpdate($application, $platform, $request->get('version'));
        $currentBuild = $this->builds->getByVersion($application, $platform, $request->get('version'));

        event(new UpdateCheck($request->get('device_id'), $application, $currentBuild));

        if ($newBuild === null) {
            return response()->json([
                'message' => 'No available update.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new ApplicationUpdateResource($newBuild);
    }

}
