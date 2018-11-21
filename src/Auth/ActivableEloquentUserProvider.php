<?php

namespace AmcLab\Baseline\Auth;

use AmcLab\Baseline\Exceptions\AuthenticationException;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ActivableEloquentUserProvider extends EloquentUserProvider {

    public function checkUser(?Authenticatable $user) {
        if ($user) {
            if (!$user->enabled) {
                throw new AuthenticationException('user_account_not_enabled', 11401);
            }

            if ($user->suspended) {
                throw new AuthenticationException('user_account_suspended', 12401);
            }
        }
        return $user;
    }

    public function retrieveById($identifier) {
        return $this->checkUser(\call_user_func_array([ parent::class, __FUNCTION__ ], \func_get_args()));
    }

    public function retrieveByToken($identifier, $token) {
        return $this->checkUser(\call_user_func_array([ parent::class, __FUNCTION__ ], \func_get_args()));
    }

    public function retrieveByCredentials(array $credentials) {
        return $this->checkUser(\call_user_func_array([ parent::class, __FUNCTION__ ], \func_get_args()));
    }

}
