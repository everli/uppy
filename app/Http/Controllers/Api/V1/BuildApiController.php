<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BuildCreateRequest;
use App\Http\Requests\Api\V1\BuildUpdateRequest;
use App\Http\Resources\DownloadResource;
use App\Http\Resources\V1\ApiResource;
use App\Http\Resources\V1\BuildApiResource;
use App\Models\Application;
use App\Models\Build;
use App\Platforms\Platform;
use App\Platforms\PlatformService;
use App\Repositories\DeviceRepository;
use App\Rules\NewVersion;
use Jenssegers\Agent\Agent;

/**
 * Class PackageController
 *
 * @package App\Http\Controllers
 */
class BuildApiController extends Controller
{

    /**
     * @param Application $application
     * @param DeviceRepository $deviceRepository
     * @param Agent $agent
     * @return BuildApiResource
     */
    public function index(Application $application, DeviceRepository $deviceRepository, Agent $agent): BuildApiResource
    {
        return new BuildApiResource(
            $this->builds->getByPlatform($application),
            $deviceRepository->getApplicationActiveDevices($application->id),
            $agent
        );
    }

    /**
     * @param  BuildCreateRequest  $request
     * @param  Application  $application
     * @param  PlatformService  $platforms
     * @return ApiResource
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(
        BuildCreateRequest $request,
        Application $application,
        PlatformService $platforms
    ): ApiResource {
        $platform = $platforms->guessFromFile($request->file('file'));

        $this->validate($request, ['version' => new NewVersion($application, $platform)]);

        $build = $this->builds->create($application, $platform, $request->validated());

        return new ApiResource($build);
    }

    /**
     * @param  Build  $build
     * @return ApiResource
     */
    public function show(Build $build): ApiResource
    {
        $build->load('changelogs');

        return new ApiResource($build);
    }

    /**
     * @param  BuildUpdateRequest  $request
     * @param  Build  $build
     * @return ApiResource
     */
    public function update(BuildUpdateRequest $request, Build $build): ApiResource
    {
        $this->builds->update($build, $request->validated());

        return new ApiResource($build);
    }

    /**
     * @param  Build  $build
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function delete(Build $build)
    {
        $this->builds->delete($build);

        return response(null, 204);
    }

    /**
     * @param  Application  $application
     * @param  Platform  $platform
     * @return DownloadResource
     */
    public function latest(Application $application, Platform $platform): DownloadResource
    {
        $build = $this->builds->getLastBuild($application, $platform);
        return new DownloadResource($build);
    }
}
