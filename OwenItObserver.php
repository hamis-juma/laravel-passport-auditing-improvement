<?php

namespace App\Observers\audit;

use App\Models\Passport\OauthAccessTokenExtension;
use App\Services\Auth\Passport\PersonalAccessTokenFactoryExtensionTrait;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

class OwenItObservable
{
    use PersonalAccessTokenFactoryExtensionTrait;
    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\Auth\User  $user
     * @return void
     */
    public function created(Audit $audit)
    {
        if ($this->isApiRequest()) {
            $token_extension = OauthAccessTokenExtension::query()->where('oauth_access_token_id', request()->input('token_id'))->first();
            if($token_extension) {
                DB::transaction(function () use ($audit,$token_extension){
                    $audit->user_type = $token_extension ? $token_extension->owner_type : NULL;
                    $audit->user_id = $token_extension ? $token_extension->owner_id : NULL;
                    $audit->save();
                });
            }
        }
    }
}

