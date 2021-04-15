<?php

namespace App\Http\Controllers;

use App\Repositories\BuildRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var BuildRepository
     */
    protected $builds;


    /**
     * UpdateController constructor.
     *
     * @param BuildRepository $buildRepository
     */
    public function __construct(BuildRepository $buildRepository)
    {
        $this->builds = $buildRepository;
    }
}
