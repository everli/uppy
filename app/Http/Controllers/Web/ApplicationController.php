<?php
declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Events\BuildDownloaded;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Build;
use App\Platforms\Platform;
use App\Platforms\PlatformService;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PackageController
 *
 * @package App\Http\Controllers
 */
class ApplicationController extends Controller
{

    /**
     * @param Application $application
     * @param Agent $agent
     * @param PlatformService $platforms
     * @return RedirectResponse
     */
    public function platformRedirect(Application $application, Agent $agent, PlatformService $platforms)
    {
        $build = $this->builds->getLastBuild($application, $platforms->matchFromAgent($agent));

        if ($build === null) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return redirect()->to('/applications/' . $application->slug . '/' . $platforms->matchFromAgent($agent));
    }

    /**
     * Redirects to the right download flow.
     *
     * @param Request $request
     * @param Cloud $storage
     * @param Application $application
     * @param Platform $platform
     * @param Build|null $build
     * @return RedirectResponse
     */
    public function install(Request $request, Cloud $storage, Application $application, Platform $platform, ?Build $build = null): RedirectResponse
    {
        $build = $build ?? $this->builds->getLastBuild($application, $platform);

        if (!$storage->exists($build->file)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        // track download event
        event(new BuildDownloaded($build, $request->userAgent()));

        return redirect()->away($platform->getDownloadUrl($application, $build, $storage));
    }

    /**
     * Show the icon of a given application.
     *
     * @param Application $application
     * @param Cloud $storage
     * @return string
     */
    public function icon(Application $application, Cloud $storage)
    {
        if (!$storage->exists($application->icon)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $storage->response($application->icon);
    }

    /**
     * Returns plist needed for iOS install.
     *
     * @param Application $application
     * @param Platform $platform
     *
     * @param Cloud $storage
     * @return Response
     */
    public function plist(Application $application, Platform $platform, Cloud $storage): Response
    {
        $build = $this->builds->getLastBuild($application, $platform);

        return response()->view('templates.plist', [
            'fileUrl' => $platform->getFileUrl($build, $storage),
            'bundleVersion' => $build->version,
            'title' => $application->name,
            'package' => $build->package,
        ])->header('Content-Type', 'text/xml');
    }
}
