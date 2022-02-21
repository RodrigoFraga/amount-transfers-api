<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

/**
 * Class RequestAuthorizeTransaction
 * @package App\Utils
 */
class RequestAuthorizeTransaction
{
    /**
     * @return bool
     */
    public static function check (): bool
    {
        $response = Http::get(config('appconfig.request_authorize_transaction.url'));

        if ($response->successful()) {
            $body = json_decode($response->body());

            return (isset($body->message) && $body->message == 'Autorizado');
        }

        // TODO: Notify reason for error to admins
        return false;
    }

}
