<?php

namespace Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

class Auth extends ShieldAuth
{
    public array $views = [
        'login' => 'auth/login',
    ];

    public bool $allowRegistration = false;
    public bool $allowMagicLinkLogins = false;

    public array $redirects = [
        'register'          => '/admin/settings',
        'login'             => '/admin/settings',
        'logout'            => 'login',
        'force_reset'       => '/admin/settings',
        'permission_denied' => '/',
        'group_denied'      => '/',
    ];

    public array $sessionConfig = [
        'field'              => 'user',
        'allowRemembering'   => false,
        'rememberCookieName' => 'remember',
        'rememberLength'     => 30 * DAY,
    ];
}
