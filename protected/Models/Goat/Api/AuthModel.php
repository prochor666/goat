<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* AuthModel - Athorization API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/

class AuthModel extends BasicAssetModel
{
    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'login' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 3],
            ],
            'password' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1],
            ]
        ];
    }


    /**
    * Session based login check
    * @return array
    */
    public function logged(): array
    {
        $user = $this->app->session('user');

        $id         = is_object($user) ? $user->id: 0;
        $username   = is_object($user) ? $user->username: '';
        $email      = is_object($user) ? $user->email: '';

        $exists = $this->existsWithData(' (id = ? AND username LIKE ? AND email LIKE ? AND active = 1)', [$id, $username, $email]);

        // Valid user, update session record
        if ($exists->id > 0) {

            $this->setUserSession($exists);
            unset(
                $exists->password,
                $exists->token,
                $exists->auth2
            );

            $this->history([
                'targetid'  => $exists->id,
                'type'      => 'user',
                'message'   => 'User login check',
                'data'      => json_encode($user),
                'username'  => $exists->username,
                'userid'    => $exists->id,
            ]);
        }

        return [
            'logged' => $exists->id > 0 ? true: false,
            //'ssid'   => session_id(),
            //'sgcp' => session_get_cookie_params(),
            'user'   => $exists,
            'ip'     => clientIp(),
        ];
    }


    /**
    * User login
    * @param array $input
    * @return array
    */
    public function login($input): array
    {
        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'logged' => false,
                'user'   => []
            ];
        }

        $exists = $this->existsWithData(' (username LIKE ? OR email LIKE ?) AND password LIKE ? AND active = 1', [$input['login'], $input['login'], getHash($input['password'])]);

        if ($exists->id > 0) {

            $this->setUserSession($exists);

            unset(
                $exists->password,
                $exists->token,
                $exists->auth2
            );
            unset($input['password']);

            $this->history([
                'targetid'  => $exists->id,
                'type'      => 'user',
                'message'   => 'User logged in',
                'data'      => $input,
                'username'  => $exists->username,
                'userid'    => $exists->id,
            ]);
        }

        return [
            'logged' => $exists->id > 0 ? true: false,
            'ssid'   => session_id(),
            'user' => $exists
        ];
    }


    /**
    * User activation
    * @param array $input
    * @return array
    */
    public function activate($input): array
    {
        $exists = $this->existsWithData('token LIKE ?', [$input['token']]);

        if ($exists->id > 0) {

            $this->setUserSession($exists);

            $this->assets->update($exists->id, [
                'token'   => '',
                'active'  => 1,
            ]);

            unset(
                $exists->password,
                $exists->token,
                $exists->auth2
            );
            unset($input['token']);

            $this->history([
                'targetid'  => $exists->id,
                'type'      => 'user',
                'message'   => 'User activated and logged in',
                'data'      => $input,
                'username'  => $exists->username,
                'userid'    => $exists->id,
            ]);
        }

        return [
            'logged' => $exists->id > 0 ? true: false,
            'user' => $exists
        ];
    }


    /**
    * User password recovery token check
    * @param array $input
    * @return array
    */
    public function recover($input): array
    {
        $exists = $this->existsWithData('username LIKE ? OR email LIKE ?', [$input['user'], $input['user']]);

        $token = rnd(200);

        if ($exists->id > 0) {

            $this->assets->update($exists->id, [
                'token'   => $token,
            ]);

            $this->history([
                'targetid'  => $exists->id,
                'type'      => 'user',
                'message'   => 'User pasword recovery',
                'data'      => $input,
                'username'  => $exists->username,
                'userid'    => $exists->id,
            ]);
        }

        return [
            'recover' => $exists->id > 0 ? true: false,
            'user' => $exists,
            'token' => $token,
        ];
    }


    /**
    * User password update
    * @param array $input
    * @return array
    */
    public function updatePassword($input): array
    {
        $exists = $this->existsWithData('username LIKE ? OR email LIKE ?', [$input['user'], $input['user']]);

        $token = rnd(200);

        if ($exists->id > 0) {

            $this->assets->update($exists->id, [
                'token'   => $token,
            ]);

            $this->history([
                'targetid'  => $exists->id,
                'type'      => 'user',
                'message'   => 'User pasword recovery',
                'data'      => $input,
                'username'  => $exists->username,
                'userid'    => $exists->id,
            ]);
        }

        return [
            'recover' => $exists->id > 0 ? true: false,
            'user' => $exists,
            'token' => $token,
        ];
    }


    /**
    * Set user session in database
    * @param object $user
    * @return void
    */
    protected function setUserSession($user): void
    {
        $temporaryType = $this->assets->swapType('sessions');

        $exists = $this->existsWithData(' (user = ? AND session LIKE ?)', [$user->id, session_id()]);

        if ($exists->id > 0) {

            $this->assets->update($exists->id, [
                'session'       => session_id(),
                'user'          => $user->id,
                'ip'            => clientIp(),
                'last_access'   => date('Y-m-d H:i:s', time()),
            ]);

        } else {

            $this->assets->create([
                'session'       => session_id(),
                'user'          => $user->id,
                'ip'            => clientIp(),
                'last_access'   => date('Y-m-d H:i:s', time()),
                'created_at'    => date('Y-m-d H:i:s', time()),
            ]);
        }

        $this->assets->setType($temporaryType);
        $this->app->session('user', $user);
    }


    /**
    * Session based logout
    * @return array
    */
    public function logout(): array
    {
        $user = $this->app->session('user');

        $id = is_object($user) ? $user->id: 0;
        $username = is_object($user) ? $user->username: '';
        $email = is_object($user) ? $user->email: '';

        $exists = $this->existsWithData(' (id = ? AND username LIKE ? AND email LIKE ?)', [$id, $username, $email]);

        if ($exists->id > 0) {

            $this->deleteUserSession($exists);
            unset(
                $exists->password,
                $exists->token,
                $exists->auth2
            );

            $this->history([
                'targetid'  => $exists->id,
                'type'      => 'user',
                'message'   => 'User logout',
                'data'      => $exists,
                'username'  => $exists->username,
                'userid'    => $exists->id,
            ]);
        }

        session_destroy();
        $sessionID = session_create_id();
        session_commit();
        session_id($sessionID);
        session_start();

        return [
            'logged' => false,
            'user' => [
                'id' => 0
            ]
        ];
    }


    /**
    * Data based logout
    * @return array
    */
    public function logoutGlobal(): array
    {
        $user = $this->app->session('user');

        $id = is_object($user) ? $user->id: 0;
        $username = is_object($user) ? $user->username: '';
        $email = is_object($user) ? $user->email: '';

        $exists = $this->existsWithData(' (id = ? AND username LIKE ? AND email LIKE ?)', [$id, $username, $email]);

        if ($exists->id > 0) {

            unset($exists->password);
        }

        return [
            'logged' => false,
            'user' => []
        ];
    }


    /**
    * Delete session data from database, reset session data
    * @param object $user
    * @return void
    */
    protected function deleteUserSession($user): void
    {
        $temporaryType = $this->assets->swapType('sessions');

        $exists = $this->existsWithData(' (user = ? AND session LIKE ?)', [$user->id, session_id()]);

        if ($exists->id > 0) {

            $this->assets->delete($exists->id);
        }

        $this->assets->setType($temporaryType);
        $this->app->session('user', []);
    }


    /**
    * Notify user
    * @param object $user
    * @return void
    */
    protected function notify($user): void
    {
        $temporaryType = $this->assets->swapType('sessions');

        $exists = $this->existsWithData(' (user = ? AND session LIKE ?)', [$user->id, session_id()]);

        if ($exists->id > 0) {

            $this->assets->delete($exists->id);
        }

        $this->assets->setType($temporaryType);
        $this->app->session('user', []);
    }
}