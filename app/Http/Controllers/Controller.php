<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Returns an error message in standardized format.
     *
     * @param string|array $message
     * @param int $code
     * @param \Exception|null $e
     * @return JsonResponse
     */
    protected function error( $message = '', int $code = 500, \Exception $e = null): JsonResponse
    {
        $response = [ 'error' => true ];

        if (is_string($message)) {
            $message = ['error' => [$message ?: 'Ocorreu um erro na sua solicitação.']];
        }

        $response['message'] = [ $message ];

        if ($e) {
            $error =  [
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];

            if (env('APP_ENV') == 'local') {
                $response['trace'] = $error;
            }

            Log::error($e->getMessage(), $error);
        }

        return response()->json($response, $code);
    }
}
