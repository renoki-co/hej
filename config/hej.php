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

    'default_authenticatable' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | Specify the route names to use as redirects after different actions.
    | These can be also overwritten if you extend the controller class.
    |
    */

    'redirects' => [
        'authenticated' => 'home',
        'provider_rejected' => 'home',
        'duplicate_email' => 'home',
        'provider_already_linked' => 'home',
        'provider_linked_to_another' => 'home',
        'link' => 'home',
        'unlink' => 'home',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Providers
    |--------------------------------------------------------------------------
    |
    | This will overwrite the list of allowed providers within the controller.
    |
    */

    'allowed_providers' => [
        // 'facebook',
        // 'twitter',
        // 'github',
    ],

];
