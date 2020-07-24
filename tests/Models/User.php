<?php

namespace RenokiCo\Hej\Test\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use RenokiCo\Hej\Concerns\HasSocialAccounts;
use RenokiCo\Hej\Contracts\Sociable;

class User extends Authenticatable implements Sociable
{
    use HasSocialAccounts;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
