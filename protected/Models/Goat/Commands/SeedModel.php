<?php
namespace Goat\Commands;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* SeedModel - seeding command model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class SeedModel extends BasicAssetModel
{
    protected $app;

    protected $data;

    use \GoatCore\Traits\Disk;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->data = json_decode($this->readFile("{$this->app->config('fsRoot')}/protected/seed/seed.json"), true);

        $this->predefined = [
            'username' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 3, 'max' => 255],
            ],
            'email' => [
                'validation_method'    => 'email',
                'default'              => '',
            ],
            'password' => [
                'validation_method'    => 'password',
                'default'              => '',
                'options'              => ['level' => 1],
            ],
            'firstname' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              =>  ['max' => 255],
            ],
            'lastname' => [
                'validation_method'    => 'string',
                'default'              => 0,
                'options'              => ['max' => 255],
            ],
            'role' => [
                'validation_method'    => 'string',
                'default'              => 'user',
            ],
        ];
    }


    public function release(): array
    {
        $result = [
            'history_created' => [],
            'users_created' => [],
            'session_created' => [],
        ];

        $varchar = str_repeat('a', 255);
        $blob = str_repeat('a', 1000);

        $this->history([
            'targetid'      => PHP_INT_MAX,
            'type'          => $varchar,
            'message'       => $blob,
            'data'          => ['init' => $blob],
            'username'      => $varchar,
            'userid'        => PHP_INT_MAX,
        ]);

        $this->assets->create($this->extend([
            'username'      => $varchar,
            'email'         => $varchar,
            'password'      => $varchar,
            'firstname'     => $varchar,
            'lastname'      => $varchar,
            'token'         => str_repeat('a', 100),
            'active'        => 0,
            'role'          => $varchar,
        ], 'create'));

        $this->assets->wipe();
        $this->assets->wipe('history');

        foreach($this->data['history'] as $history) {

            $result['history_created'][] = $this->history([
                'targetid'  => $history['targetid'],
                'type'      => $history['type'],
                'message'   => $history['message'],
                'data'      => $history['data'],
                'username'  => $history['username'],
                'userid'    => $history['userid']
            ]);
        }

        foreach($this->data['users'] as $user) {

            $user = $this->normalize($user, $setDefaults = true);
            $user = $this->extend($user, 'create');

            $user['password'] = getHash($user['password']);
            $user['token'] = rnd(100);
            $user['auth2'] = str_repeat('0', 6);

            $cid = $this->assets->create($user);
            $result['users_created'][] = $cid;

            unset($user['password']);

            $result['history_created'][] = $this->history([
                'targetid'  => $cid,
                'type'      => 'user',
                'message'   => 'User created',
                'data'      => $user,
                'username'  => '',
                'userid'    => 0
            ]);
        }

        return $result;
    }
}

