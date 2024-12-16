<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Aqui você pode registrar qualquer exceção para o log, por exemplo.
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        // ModelNotFoundException (Erro 404)
        if ($exception instanceof ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }

        // UnauthorizedHttpException (Erro 403)
        if ($exception instanceof UnauthorizedHttpException) {
            return response()->view('errors.403', [], 403); 
        }

        // BadRequestHttpException (Erro 400)
        if ($exception instanceof BadRequestHttpException) {
            return response()->view('errors.400', [], 400);
        }

        // TokenMismatchException (Erro 419)
        if ($exception instanceof TokenMismatchException) {
            return response()->view('errors.419', [], 419); 
        }

        // HttpException (Erro 500)
        if ($exception instanceof HttpException && $exception->getStatusCode() === 500) {
            return response()->view('errors.500', [], 500); 
        }

        return parent::render($request, $exception);
    }

}
