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
    protected $app, $langService, $storageService, $resources;

    public function __construct(GoatCore $app)
    {
        $this->app = $app;
        $this->predefined = [];
        $this->langService = $this->app->store->entry('Goat\Lang');
        $this->storageService = $this->app->store->entry('Goat\Storage');
        $this->resources = [
            'langFile' => $this->app->config('fsRoot') . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ISO-639.json',
        ];
    }


    public function langs($input = [])
    {
        $data = [
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

                $data['langs'] = $this->langService->loadAll(
                                        json_decode($this->storageService->readFile($this->resources['langFile']), true), $reduce);
                $data['status'] = true;
                $data['message'] = count($reduce) > 0 ? 'Ok, reduced dataset sent': 'Ok, full dataset sent';

            }catch (\Exception $e) {

                $data['status'] = false;
                $data['message'] = 'Error parsing ISO-639.json file, setting up defaults';

                return $data;
            }
        }

        return $data;
    }
}