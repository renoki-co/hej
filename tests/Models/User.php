<?php

namespace RenokiCo\Hej\Test\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use RenokiCo\Hej\Concerns\HasSocialAccounts;

class User extends Authenticatable
{
    use HasSocialAccounts;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
