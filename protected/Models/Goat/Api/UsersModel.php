<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* UserModel - User API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class UsersModel extends BasicAssetModel
{
    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'username' => [
                'validation_method'    => 'string',
                'required'             => true,
                'options'              => ['min' => 3, 'max' => 255],
            ],
            'email' => [
                'validation_method'    => 'email',
                'required'              => true,
            ],
            'password' => [
                'validation_method'    => 'password',
                'required'             => true,
                'options'              => ['level' => (int)$this->app->config('passwordSecurity')],
            ],
            'firstname' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              =>  ['max' => 255],
            ],
            'lastname' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['max' => 255],
            ],
            'role' => [
                'validation_method'    => 'string',
                'default'              => 'user',
                'options'              =>  ['max' => 20],
            ],
        ];
    }


    /**
    * Create new user
    * @param array $input
    * @return array
    */
    public function create($input): array
    {
        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
                'method' => 'post',
            ];
        }

        // Check same host
        $exists = $this->existsWithData(' username LIKE ? OR email LIKE ? ', [$input['username'], $input['email']]);

        if ($exists->id > 0) {

            $existsKey = $exists->username == $input['username'] ? 'Username': 'Email';

            return [
                'error' => "{$existsKey} already exists",
                'input' => $input,
            ];
        }

        $input = $this->extend($input, 'create');

        $input['password'] = getHash($input['password']);
        $input['token'] = rnd(200);
        $input['auth2'] = 0;
        $input['active'] = 0;

        return [
            'created' => $this->assets->create($input),
        ];
    }


    /**
    * Full update existing user
    * @param int $id
    * @param array $input
    * @return array
    */
    public function update($id, $input): array
    {
        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
               'error' => "Invalid dataset",
               'input' => $input,
               'method' => 'put',
            ];
        }

        // Check same user and exclude updated id
        $exists = $this->existsWithData(' (username LIKE ? OR email LIKE ?) AND id != ? ', [$input['username'], $input['email'], $id]);

        if ($exists->id > 0) {

            $existsKey = $exists->username == $input['username'] ? 'Username': 'Email';

            return [
                'error' => "{$existsKey} already exists",
            ];
        }

        $input = $this->extend($input, 'update');

        $input['password'] = getHash($input['password']);

        return [
            'updated' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Partial update for existing user
    * @param int $id
    * @param array $input
    * @return array
    */
    public function patch($id, $input): array
    {
        unset($this->predefined['password']);
        $input = $this->normalize($input);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
                'method' => 'patch',
            ];
        }

        // Check same user and exclude updated id
        $exists = $this->existsWithData(' (username LIKE ? OR email LIKE ?) AND id != ? ', [$input['username'], $input['email'], $id]);

        if ($exists === true) {

            $existsKey = $exists->username == $input['username'] ? 'Username': 'Email';

            return [
                'error' => "{$existsKey} already exists",
            ];
        }

        $input = $this->extend($input, 'update');

        return [
            'patched' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Hard & soft delete existing user
    * @param int $id
    * @param bool $soft
    * @return array
    */
    public function delete($id, $soft = true): array
    {
        // Soft delete, just sign deleted
        if ($soft === true) {

            $input = $this->extend([], 'delete');
            return [
                'deleted' => $this->assets->update($id, $input),
                'soft' => $soft,
            ];
        }

        // Hard delete, SQL DELETE
        return [
            'deleted' => $this->assets->delete($id),
            'soft' => $soft,
        ];
    }
}