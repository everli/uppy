<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws ApiException
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ApiException) {
            return response()
                ->json($exception, $exception->getStatus());
        }

        // This is needed because the content type of `applications.create` and `builds.create` routes
        // is multipart/form-data content (due to the file upload)
        // and not application/json as expected by Laravel for properly
        // render the errors as json
        if ($request->is("api/*")) {
            if ($exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException) {
                throw new ApiException(
                    Response::HTTP_NOT_FOUND,
                    'URI not found'
                );
            }

            if ($exception instanceof ValidationException) {
                throw new ApiException(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $exception->getMessage(),
                    $exception->errors()
                );
            }

            if ($exception instanceof PostTooLargeException) {
                throw new ApiException(
                    Response::HTTP_REQUEST_ENTITY_TOO_LARGE,
                    'The uploaded file cannot exceed ' . ini_get('post_max_size')
                );
            }
        }

        return parent::render($request, $exception);
    }
}
