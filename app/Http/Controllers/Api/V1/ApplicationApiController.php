<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApplicationCreateRequest;
use App\Http\Requests\Api\V1\UpdateGetRequest;
use App\Http\Resources\V1\ApiResource;
use App\Http\Resources\V1\ApplicationEventsResource;
use App\Http\Resources\V1\ApplicationUpdateResource;
use App\Models\Application;
use App\Platforms\Platform;
use App\Repositories\ApplicationRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Class ApplicationApiController
 * @package App\Http\Controllers\Api\V1
 */
class ApplicationApiController extends Controller
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // eager load application with builds
        $applications = Application::with([
            'builds' => function ($query) {
                // match the application builds in...
                return $query->whereIn(DB::raw('(application_id, platform, available_from)'), function ($query) {
                    // ...application id and platform, with the latest available build
                    return $query->select(['application_id', 'platform', DB::raw('MAX(available_from)')])
                        ->from('builds')
                        ->where('available_from', '<=', Carbon::now()->toDateTimeString()) // that are actually available
                        ->groupBy(['application_id', 'platform']); // grouped by platform and application, and we get always the last version for each app and version
                })->orderBy('platform'); // order the builds by platform
            }
        ])->get();

        return response()->json($applications);
    }

    /**
     * @param Application $application
     * @return ApiResource
     */
    public function get(Application $application): ApiResource
    {
        return new ApiResource($application);
    }


    /**
     * @param ApplicationCreateRequest $request
     * @param ApplicationRepository $applications
     * @return ApiResource
     */
    public function create(ApplicationCreateRequest $request, ApplicationRepository $applications): ApiResource
    {
        $application = $applications->create($request->validated());

        return new ApiResource($application);
    }

    /**
     * Returns the events related to the application.
     *
     * @param Application $application
     * @return ApplicationEventsResource
     */
    public function events(Application $application): ApplicationEventsResource
    {
        return new ApplicationEventsResource($application->builds()->orderBy('platform')->orderBy('created_at', 'desc')->get());
    }


    /**
     * @param UpdateGetRequest $request
     *
     * @param Application $application
     * @param Platform $platform
     * @param string $version
     * @return ApplicationUpdateResource|JsonResponse
     *
     * @see \App\Http\Controllers\Api\V2\ApplicationApiController
     *
     * @deprecated use V2 instead
     */
    public function updates(UpdateGetRequest $request, Application $application, Platform $platform, string $version)
    {
        $availableBuild = $this->builds->getUpdate($application, $platform, $version);

        if ($availableBuild === null) {
            return response()->json([
                'message' => 'No available update.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new ApplicationUpdateResource($availableBuild);
    }
}
