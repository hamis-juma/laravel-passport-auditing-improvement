<?php

namespace App\Http\Middleware;

use App\Services\Auth\Passport\PersonalAccessTokenFactoryExtensionTrait;
use Closure;
use Laravel\Passport\Passport;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class PassportMiddleware extends Middleware
{
    use PersonalAccessTokenFactoryExtensionTrait;

    public function handle($request, Closure $next, ...$guards)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Unauthorized. Token not provided.'], 401);
        }
        if(!preg_match('/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $token)) {
            return response()->json(['error' => 'Token is not in the right format.'], 401);
        }
        $passportToken = Passport::token()->where('id', $this->getToken($token)->id)->first();

        if (!$passportToken || $passportToken->revoked) {
            return response()->json(['error' =>'Unauthorized. Invalid or revoked token.'], 401);
        }
        // add token id in a request
        request()->merge(['token_id' => $passportToken->id]);
        return $next($request);
    }
}
