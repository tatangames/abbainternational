<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
class Handler extends ExceptionHandler
{


    public function render($request, Throwable $exception)
    {
        /*if ($exception instanceof TokenInvalidException) {
            return response()->json(['error' => 'Token no válido'], 401);
        }

        if ($exception instanceof UnauthorizedHttpException && $exception->getPrevious() instanceof TokenInvalidException) {
            return response()->json(['error' => 'Token no válido'], 401);
        }

        // Añade manejo para otras excepciones relacionadas con JWT si es necesario
        if ($exception instanceof JWTException) {
            return response()->json(['error' => 'Error al procesar el token Handle'], 401);
        }*/

        // Si la excepción es TokenInvalidException y la solicitud es una API
        if ($exception instanceof TokenInvalidException && $request->is('api/*')) {
            return response()->json(['error' => 'Token no válidox'], 401);
        }

        // Si la excepción es UnauthorizedHttpException y la solicitud es una API
        if ($exception instanceof UnauthorizedHttpException && $request->is('api/*')) {
            return response()->json(['error' => 'No autorizado Handler'], 401);
        }

        if ($exception instanceof JWTException && $request->is('api/*')) {
            return response()->json(['error' => 'Error al procesar el token Handle'], 401);
        }

        return parent::render($request, $exception);
    }


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

            /*$this->renderable(function(TokenInvalidException $e, $request){
                return Response::json(['error'=>'Invalid token'],401);
            });
            $this->renderable(function (TokenExpiredException $e, $request) {
                return Response::json(['error'=>'Token has Expired'],401);
            });

            $this->renderable(function (JWTException $e, $request) {
                return Response::json(['error'=>'Token not parsed'],401);
            });*/


        });
    }
}
