<?php
namespace Goat\Api;

use GoatCore\GoatCore;

/**
* HelpersModel - Helpers data API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class HelpersModel
{
    protected $app, $langService, $storageService, $resources, $predefined;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->predefined = [];
        $this->langService = $this->app->store->entry('Goat\Lang');
        $this->storageService = $this->app->store->entry('Goat\Storage');
        $this->resources = [
            'langFile' => $this->app->config('fsRoot') . '/config/ISO-639.json',
        ];
    }


    public function langs($input = [])
    {
        $result = [
            'langs' => [
                [
                    'name'     => 'English',
                    'alpha2'   => 'en',
                    'alpha3-b' => 'eng'
                ],
            ],
            'message' => 'Default setting, ISO-639.json file not found',
            'status'  => true,
            'input'   => $input,
        ];

        $reduce = ark($input, 'reduce', $this->app->config('lang')['reduce']);

        if ($this->storageService->isFile($this->resources['langFile'])) {

            try {

                $result['langs'] = $this->langService->loadAll(
                                        json_decode($this->storageService->readFile($this->resources['langFile']), true), $reduce);
                $result['status'] = true;
                $result['message'] = count($reduce) > 0 ? 'Ok, reduced dataset sent': 'Ok, full dataset sent';

            }catch (\Exception $e) {

                $result['status'] = false;
                $result['message'] = 'Error parsing ISO-639.json file, setting up defaults';

                return $result;
            }
        }

        return $result;
    }


    public function roles($input = [])
    {
        $result = [
            'roles' => $this->app->config('roles'),
            'status' => true,
        ];

        return $result;
    }
}