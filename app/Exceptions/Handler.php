<?php

namespace App\Exceptions;

use App\Log;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \App\Exceptions\PurNotVerifiedException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }
        $data = [
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString(),
        ];

        $dataArr =[
            'file'           => $data['file'],
            'error_summary'  => 'Line '.$data['line'].' '.$data['message'],
            'log_trace'      => $data['trace']
        ];
        // Log::create($dataArr);
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return mixed
     */
    public function render($request, Exception $exception)
    {
        // This will replace our 404 response with
        // a JSON response // && $request->wantsJson()
        if($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json(['error' => 'Token is Invalid', 'code_status' => '02'], 400);
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json(["error" => $exception->getMessage(), 'code_status' => '02'], 401);
        } else if($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
            return response()->json(['error' => 'There is problem with your token', 'code_status' => '02'], 400);
        }
        // else {
        //     return response()->json(['error' => 'There is problem with your token', 'code_status' => '02'], 400);
        // }
        if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
            return response()->json(
                [
                'errors' => [
                    'message' => 'Resource data missing',
                ],
                ],
                404
            );
        }


        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect()->route('login');
        }

        // if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
        //     if ($request->ajax()) {
        //         return response()->view('errors.modal.403');
        //     }
        //     return redirect('/error/403');
        // }

        // if ($exception instanceof UserNotVerifiedException) {
        //     return response()->view('vendor.laravel-user-verification.user-verification', [], 401);
        // }
        if ($exception instanceof PurNotVerifiedException) {
            return response()->view('errors.license', [], 401);
        }
        // if ($exception instanceof \PDOException) {
        //     return response()->view('errors.dbconnect', [], 500);
        // }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return mixed
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.', 'code_status' => '02'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
