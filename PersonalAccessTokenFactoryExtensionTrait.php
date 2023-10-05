<?php

namespace App\Services\Auth\Passport;

use App\Models\Passport\OauthAccessTokenExtension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;

trait PersonalAccessTokenFactoryExtensionTrait
{

    protected $tokens;
    protected $jwt;

    /**
     * @param TokenRepository $tokens
     * @param JwtParser $jwt
     */
    public function __construct(TokenRepository $tokens, JwtParser $jwt)
    {
       $this->tokens = $tokens;
       $this->jwt = $jwt;
    }

    /**
     * @param $token
     * @return Token
     */
    protected function getToken($token)
    {
        return $this->findAccessToken($token);
    }

    /**
     * Get the access token instance for the parsed response.
     *
     * @param  array  $response
     * @return \Laravel\Passport\Token
     */
    protected function findAccessToken($token)
    {
        return $this->tokens->find(
            $this->jwt->parse($token)->claims()->get('jti')
        );
    }

    /**
     * @param Model $model
     * @param $token_result
     * @return mixed
     */
    protected function createExtension(Model $model, $token_result)
    {
        return DB::transaction(function () use ($model, $token_result){
            return OauthAccessTokenExtension::create([
                'oauth_access_token_id' => $token_result->token->id,
                'owner_id' => $model->id,
                'owner_type' => get_class($model),
            ]);
        });
    }


    /**
     * @return bool
     */
    protected function isApiRequest()
    {
        $acceptHeader = request()->header('Accept');
        $xmlHttpRequestHeader = request()->header('X-Requested-With');
        $requestPath = request()->path();

        // Check if the "Accept" header indicates JSON, "X-Requested-With" is set to "XMLHttpRequest,"
        // or the request route starts with "/api/"
        return (
            strpos($acceptHeader, 'application/json') !== false ||
            $xmlHttpRequestHeader === 'XMLHttpRequest' ||
            strpos($requestPath, 'api/') === 0
        );
    }
}

