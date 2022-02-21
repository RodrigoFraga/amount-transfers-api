<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse [string] access_token
     * @throws ValidationException
     */
    public function login (Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'       => 'required|string',
            'password'    => 'required|string',
            'remember_me' => 'boolean'
        ]);

        if ($validator->fails()) throw new ValidationException($validator);

        $credentials = ['email' => strtolower($request->login), 'password' => $request->password];
        if (!Auth::attempt($credentials)) {
            $credentials = ['document' => $request->login, 'password' => $request->password];
            if (!Auth::attempt($credentials))
                abort(401, 'Login or password invalid');
        }

        $user = $request->user();

        $loggedUser = $this->getLoggedUser($user);

        return response()->json($loggedUser);
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
            $user = $request->user();
            $user->token()->revoke();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $user
     * @param null $rememberMe
     * @return array
     */
    public function getLoggedUser ($user, $rememberMe = null): array
    {
        $tokenResult = $user->createToken('Transfers');
        $token       = $tokenResult->token;

        if ($rememberMe)
            $token->expires_at = Carbon::now()->addWeeks();

        $token->save();

        return [
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'          => $user->id,
                'name'        => $user->name,
                'role'        => strtoupper($user->role),
                'email'       => $user->email,
                'document'    => $user->document,
                'permissions' => $this->permissions($user),
            ],
            'expires_at'   => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ];
    }


    /**
     * @param $user
     * @return array
     */
    private function permissions ($user): array
    {
        return $user->getAllPermissions()->values()->map(function ($value) use ($user) {
            return $value->name;
        })->toArray();
    }

}
