<?php
/* ****************************
| Goat app configuration
***************************** */

$config = [

    // Disk permissions
    'storage' => [
        'filePermission'         => 0644,
        'directoryPermission'    => 0755,
    ],

    // Sessions
    // See: https://www.php.net/manual/en/function.session-cache-limiter.php
    // See: https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-samesite
    'session' => [
        'cache_expire'      => 3600*12,
        'cache_limiter'     => 'nocache', // nocache | private | private_no_expire | public
        'cookie_samesite'   => 'Strict', // None | Strict | Lax
        'cookie_httponly'   => true,
        'use_only_cookies'  => true,
        'cookie_secure'     => false,
    ],

    // Default response headers
    'headers' => [
        "X-Powered-By: Chaos-power v 1.0",
        "Access-Control-Allow-Origin: *",
        "Access-Control-Allow-Methods: GET, HEAD, OPTIONS, POST, PUT, PATCH, DELETE",
        "Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin",
        "X-Content-Type-Options: nosniff",
        "Content-Security-Policy: default-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src * data: blob:; media-src * data: blob:",
        "X-Frame-Options: SAMEORIGIN",
        "X-XSS-Protection: 1",
        "Referrer-Policy: no-referrer-when-downgrade",
        "Strict-Transport-Security: max-age=2592000; includeSubDomains; preload",
    ],


    'image' => [
        'allowed' => [
            'image/png',
            'image/jpg',
            'image/jpeg',
            'image/gif',
        ],

        'useImageMagick' => true,

        'sizes' => [
            'ico'   => [64, 90],
            'xs'    => [320, 90],
            'md'    => [800, 90],
            'lg'    => [1200, 90],
        ],
    ],


    // User roles
    'roles' => [
        'admin',
        'user',
        'block',
    ],

    // Password complexity level
    'passwordSecurity' => 3,

    // Acl tokens
    'acl' => [
        'domain' => [
            'edit',
            'delete',
            'content',
        ],
        'nav' => [
            'edit',
            'delete',
            'content',
        ],
        'page' => [
            'edit',
            'delete',
            'content',
        ],
        'post' => [
            'edit',
            'delete',
            'content',
        ],
    ],

    // API endpoints
    'api' => [
        'test' => [
            'class'     => 'Goat\Api\Test',
            'method'    => 'setup',
        ],
        'users' => [
            'class'     => 'Goat\Api\Users',
            'method'    => 'setup',
        ],
        'settings' => [
            'class'     => 'Goat\Api\Settings',
            'method'    => 'setup',
        ],
        'login' => [
            'class'     => 'Goat\Api\Auth',
            'method'    => 'setup',
        ],
        'domains' => [
            'class'     => 'Goat\Api\Domains',
            'method'    => 'setup',
        ],
        'navs' => [
            'class'     => 'Goat\Api\Navs',
            'method'    => 'setup',
        ],
        'pages' => [
            'class'     => 'Goat\Api\Pages',
            'method'    => 'setup',
        ],
        'posts' => [
            'class'     => 'Goat\Api\Posts',
            'method'    => 'setup',
        ],
        'files' => [
            'class'     => 'Goat\Api\FileUpload',
            'method'    => 'setup',
        ],
        'directory' => [
            'class'     => 'Goat\Api\Directory',
            'method'    => 'setup',
        ],
    ],

    // Cli commands
    'commands' => [
        'test' => [
            'class'     => 'Goat\Commands\Test',
            'method'    => 'setup',
        ],
        'seed' => [
            'class'     => 'Goat\Commands\Seed',
            'method'    => 'setup',
        ],
        'imageinfo' => [
            'class'     => 'Goat\Commands\ImageInfo',
            'method'    => 'setup',
        ],
    ]
];
