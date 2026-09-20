<?php

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public array $groups = [
        'admin' => [
            'title' => 'Admin',
            'description' => 'Studio administrators.',
        ],
        'customer' => [
            'title' => 'Customer',
            'description' => 'Customers with read-only portal access.',
        ],
        'user' => [
            'title' => 'User',
            'description' => 'Authenticated users without workspace access.',
        ],
    ];

    public array $permissions = [];

    public array $matrix = [
        'admin' => [],
        'customer' => [],
        'user' => [],
    ];
}
