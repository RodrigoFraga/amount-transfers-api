<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

/**
 * Class Notifier
 * @package App\Utils
 */
class Notifier
{
    /**
     * @return void
     */
    public static function send ()
    {
        $response = Http::post(config('appconfig.notifier.url'));

        if ($response->failed()) {
            abort(400, 'Failed to communicate with Notifier api');
        }
    }

}
