<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* SettingsModel - User settings API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class SettingsModel extends BasicAssetModel
{
    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'notify_user_change' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'notify_domain_change' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'notify_page_change' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'notify_post_change' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'notify_errors' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
        ];
    }


    /**
    * Full update/recreate existing user settings
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
                'dataset' => $input,
            ];
        }

        $user = $this->createUserRelation($id);

        if ($user->id > 0) {

            return $this->saveSettings($input, $user);
        }

        return [
            'error' => 'User unknown'
        ];
    }


    protected function saveSettings($input, $user)
    {
        $created = 0;
        $updated = 0;
        $newSettings = [];

        // Iterate over the settings object
        foreach($input as $setting => $value) {

            // Check same user setting and update it
            $exists = $this->existsWithData(' setting LIKE ? AND users_id = ? ', [$setting, $user->id]);

            if ($exists->id > 0) {

                // Update
                $set = $this->extend(['setting' => $setting, 'value' => $value], 'update');
                $this->assets->update($exists->id, $set);
                $updated++;

            } else {

                // Create if not exist
                $newSettings[] = $this->extend(['setting' => $setting, 'value' => $value], 'create');
                $created++;
            }
        }

        $this->assets->oneToMany($user, $this->assets->getType(), $newSettings);

        return [
            'user'    => $user,
            'updated' => $updated,
            'created' => $created,
        ];
    }


    protected function createUserRelation($id)
    {
        $temporaryType = $this->assets->swapType('users');
        $user = $this->assets->one($id);
        $this->assets->setType($temporaryType);
        return $user;
    }
}