<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Here you can configure the model classes to be used by the package.
    | If you wish to extend certain functionalities, you can extend the models
    | and replace the full class name here.
    |
    */

    'models' => [

        'social' => \RenokiCo\Hej\Social::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Authenticatable
    |--------------------------------------------------------------------------
    |
    | When logging in or registering with Hej!, it will assume the
    | authenticatable it needs to register or login with is the default
    | User class. However, you can change it here if you have a different
    | Authenticatable model or you can also specify it in the controller
    | when you extend it.
    |
    */

    'default_authenticatable' => \App\User::class,

];
