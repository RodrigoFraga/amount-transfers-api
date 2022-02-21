<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $service;

    public function __construct (AuthService $service)
    {
        $this->service = $service;
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse [string] access_token
     */
    public function login (Request $request): JsonResponse
    {
        try {
            return $this->service->login($request);

        } catch (ValidationException $v) {
            return $this->error($v->errors(), $v->status);

        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return string [string] message
     */
    public function logout (Request $request): string
    {
        try {
            return $this->service->logout($request);

        } catch (Exception $e) {
            if (method_exists($e, 'getStatusCode'))
                return $this->error($e->getMessage(), $e->getStatusCode());

            return $this->error($e->getMessage());
        }
    }

}
