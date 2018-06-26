<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $code = $e->getCode();
        $message = $e->getMessage();
        if (empty($code) && method_exists($e, 'getStatusCode')) {
            $code = $e->getStatusCode();
        }
        if (empty($message) && method_exists($e, 'getStatusMessage')) {
            $message = $e->getStatusMessage();
        }
        if ($message === '') {
            $message = get_class($e);
        }

        $return = [
            'code' => $code,
            'exception' => get_class($e),
            'message' => $message,
        ];

        if (env('APP_ENV') === 'local') {
            $return['trace'] = array_map(function ($item) {
                $info = [];
                if (isset($item['file'])) {
                    $info['file'] = substr($item['file'], strlen(app()->basePath()));
                    $info['line'] = $item['line'];
                }
                return $info;
            }, $e->getTrace());
        }

        return response()->json($return);
    }
}
