<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* DomainsModel - Domain API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class DomainsModel extends BasicAssetModel
{
    protected $langService, $storageService, $resources;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->langService = $this->app->store->entry('Goat\Lang');
        $this->storageService = $this->app->store->entry('Goat\Storage');
        $this->resources = [
            'langFile' => $this->app->config('fsRoot') . '/config/ISO-639.json',
        ];

        $this->predefined = [
            'name' => [
                'validation_method'    => 'string',
                'default'              => '',
                'required'             => true,
                'options'              => ['min' => 0, 'max' => 255],
            ],
            'host' => [
                'validation_method'    => 'domain',
                'default'              => '',
                'required'             => true,
                'options'              => false,
            ],
            'public' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'required'             => true,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'langs' => [
                'validation_method'    => 'array',
                'required'             => true,
                'options'              => [
                    'validation_method' => 'lang',
                ],
            ],
            'lang_default' => [
                'validation_method'    => 'string',
                'required'             => true,
                'options'              => ['min' => 1, 'max' => 3],
            ],
            'aliases' => [
                'validation_method'    => 'array',
                'default'              => [],
                'required'             => false,
                'options'              => [
                    'validation_method' => 'domain',
                ],
            ]
        ];
    }


    /**
    * Create new domain
    * @param array $input
    * @return array
    */
    public function create($input): array
    {
        $this->predefined['langs']['options']['langs'] = $this->langService->loadAll(
            json_decode($this->storageService->readFile($this->resources['langFile']), true));

        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
            ];
        }

        // Check same host
        $exists = $this->exists(' host LIKE ? ', [$input['host']]);

        if ($exists === true) {

            return [
                'error' => "Domain host already exists",
            ];
        }

        // Check same alias host
        $temporaryType = $this->assets->swapType('aliases');

        foreach ($input['aliases'] as $alias) {

            $aliasExists = $this->exists(' host LIKE ? ', [$alias]);

            if ($aliasExists === true) {

                return [
                    'error' => "Domain alias {$alias} already exists",
                ];
            }
        }

        $this->assets->setType($temporaryType);

        $input = $this->extend($input, 'create');

        // Related values for dommain aliases
        $aliases = $input['aliases'];
        $langs = $input['langs'];
        unset($input['aliases']);
        unset($input['langs']);

        $created = $this->assets->create($input);

        if ($created > 0) {

            $domain = $this->createDomainRelation($created);
            $aliasesList = $this->saveDomainAliasesList($aliases, $domain);
            $langsList = $this->saveDomainLangsList($langs, $domain);
            //$aliasesList = [];

            return [
                'created' => $created,
                'aliases' => $aliasesList,
                'langs'   => $langsList,
            ];
        }

        return [
            'error' => 'Some SQL error'
        ];
    }


    /**
    * Full update existing domain
    * @param int $id
    * @param array $input
    * @return array
    */
    public function update($id, $input): array
    {
        $this->predefined['langs']['options']['langs'] = $this->langService->loadAll(
            json_decode($this->storageService->readFile($this->resources['langFile']), true));

        $al = ark($input, 'langs', 'XXX');

        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
                'test' => $this->getValidationMessages(),
            ];
        }

        // Check same host and exclude updated id
        $exists = $this->exists(' host LIKE ? AND id != ? ', [$input['host'], $id]);

        if ($exists === true) {

            return [
                'error' => "Domain host already exists",
            ];
        }

        $temporaryType = $this->assets->swapType('aliases');

        foreach ($input['aliases'] as $alias) {

            $aliasExists = $this->exists(' host LIKE ? AND domains_id != ? ', [$alias, $id]);

            if ($aliasExists === true) {

                return [
                    'error' => "Domain alias {$alias} already exists",
                ];
            }
        }

        $this->assets->setType($temporaryType);

        $input = $this->extend($input, 'update');

        // Related values for dommain aliases
        $aliases = $input['aliases'];
        $langs = $input['langs'];
        unset($input['aliases']);
        unset($input['langs']);

        $updated = $this->assets->update($id, $input);

        if ($updated > 0) {

            $domain = $this->createDomainRelation($updated);
            $aliasesList = $this->saveDomainAliasesList($aliases, $domain);
            $langsList = $this->saveDomainLangsList($langs, $domain);

            return [
                'updated' => $updated,
                'aliases' => $aliasesList,
                'langs'   => $langsList,
            ];
        }

        return [
            'error' => "Some SQL error",
        ];
    }


    /**
    * Partial update for existing domain
    * @param int $id
    * @param array $input
    * @return array
    */
    public function patch($id, $input): array
    {
        $input = $this->normalize($input);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
            ];
        }

        if (ark($input, 'host', false) !== false) {

            // Check same host and exclude updated id
            $exists = $this->exists(' host LIKE ? AND id != ? ', [$input['host'], $id]);

            if ($exists === true) {

                return [
                    'error' => "Domain host already exists",
                ];
            }
        }

        $input = $this->extend($input, 'update');

        return [
            'patched' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Attach lang and aliases for all domains
    * @param array $input
    * @return array
    */
    public function findRelated($input): array
    {
        $domains = $this->find($input);

        foreach ($domains as $key => $domain) {

            $this->related($domains[$key], 'langs');
            $this->related($domains[$key], 'aliases');
        }

        return $domains;
    }


    /**
    * Hard & soft delete existing domain
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


    /**
    * Save domain aliases
    * @param array $aliases
    * @param object $domain
    * @return array
    */
    protected function saveDomainAliasesList($aliases, $domain): array
    {
        $exists = 0;
        $newAliasList = [];

        $relatedAliases = $this->assets->getRelated($domain, 'aliases');

        if (count($relatedAliases)>0) {

            // Iterate over the existing aliases
            foreach ($relatedAliases as $key => $obj) {

                $sid = array_search($obj->host, $aliases);

                if ($sid !== false) {

                    unset($aliases[$sid]);
                } else {

                    $temporaryType = $this->assets->swapType('aliases');
                    $this->assets->delete($obj->id);
                    $this->assets->swapType($temporaryType);
                }
            }

            $this->assets->save($domain);
        }

        // Iterate over the pushed aliases
        foreach($aliases as $value) {

            // Create if not exist
            $newAliasList[] = $this->extend(['host' => $value], 'create');
        }

        $this->assets->oneToMany($domain, 'aliases', $newAliasList);

        return [
            'newAliasList'    => $newAliasList,
            'relatedAliases'  => $relatedAliases,
            'domain'          => $domain,
            'exists'          => $exists,
            'created'         => count($newAliasList),
        ];
    }


    /**
    * Save domain languages
    * @param array $langs
    * @param object $domain
    * @return array
    */
    protected function saveDomainLangsList($langs, $domain): array
    {
        $exists = 0;
        $newLangList = [];

        $relatedLangs = $this->assets->getRelated($domain, 'langs');

        if (count($relatedLangs)>0) {

            // Iterate over the existing aliases
            foreach ($relatedLangs as $key => $obj) {

                $sid = array_search($obj->alpha2, $langs);

                if ($sid !== false) {

                    unset($langs[$sid]);
                } else {

                    $temporaryType = $this->assets->swapType('langs');
                    $this->assets->delete($obj->id);
                    $this->assets->swapType($temporaryType);
                }
            }

            $this->assets->save($domain);
        }

        /* // Iterate over the pushed aliases
        foreach($langs as $value) {

            // Create if not exist
            $newLangList[] = $this->extend(['alpha2' => $value], 'create');
        } */

        $newLangList = $langs;

        $this->assets->oneToMany($domain, 'langs', $newLangList);

        return [
            'newLangList'    => $newLangList,
            'relatedLangs'  => $relatedLangs,
            'domain'          => $domain,
            'exists'          => $exists,
            'created'         => count($newLangList),
        ];
    }


    protected function createDomainRelation($id)
    {
        $domain = $this->assets->one($id);
        return $domain;
    }
}