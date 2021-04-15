<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlatformNotFoundException extends NotFoundHttpException
{
}
