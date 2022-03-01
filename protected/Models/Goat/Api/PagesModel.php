<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* PagesModel - Pages API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class PagesModel extends BasicAssetModel
{
    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'name' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1, 'max' => 255],
            ],
            'title' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1, 'max' => 255],
            ],
            'slug' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1, 'max' => 255],
            ],
            'description' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 0, 'max' => 550],
            ],
            'order' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => false,
            ],
            'posts_order' => [
                'validation_method'    => 'string',
                'default'              => 'id DESC',
                'options'              => false,
            ],
            'posts_page_count' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => false,
            ],
            'domains_id' => [
                'validation_method'    => 'int',
                'default'              => 1,
                'options'              => ['min' => 1],
            ],
            'navs_id' => [
                'validation_method'    => 'int',
                'default'              => 1,
                'options'              => ['min' => 1],
            ],
            'public' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'visible' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ]
        ];
    }


    /**
    * Create new page
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
            ];
        }

        // Check domain id
        $domain = $this->getDomain($input['domains_id']);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
            ];
        }

        // Check nav id
        $nav = $this->getNav($input['navs_id']);

        if ($nav->id === 0) {

            return [
                'error' => "Specified nav does not exist",
            ];
        }

        // Check same page slug
        if ($this->exists(' slug LIKE ? AND domains_id = ? ', [$input['slug'], $input['domains_id']]) !== false) {

            return [
                'error' => "Page with name {$input['slug']} already exists",
            ];
        }

        $input = $this->extend($input, 'create');
        $created = $this->assets->oneToMany($nav, $this->assets->getType(), [$input]);

        if ($created > 0) {

            return [
                'created' => $created,
            ];
        }

        return [
            'error' => 'Some SQL error'
        ];
    }


    /**
    * Full update existing page
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
            ];
        }

        // Check domain id
        $domain = $this->getDomain($input['domains_id']);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
            ];
        }

        // Check nav id
        $nav = $this->getNav($input['navs_id']);

        if ($nav->id === 0) {

            return [
                'error' => "Specified nav does not exist",
            ];
        }

        // Check same page slug and exclude updated id
        $exists = $this->exists(' slug LIKE ? AND domains_id = ? AND id != ? ', [$input['slug'], $input['domains_id'], $id]);

        if ($exists === true) {

            return [
                'error' => "Page with slug {$input['slug']} already exists",
            ];
        }

        $input = $this->extend($input, 'update');
        $updated = $this->assets->update($id, $input);

        if ($updated > 0) {

            return [
                'updated' => $updated,
            ];
        }

        return [
            'error' => "Some SQL error",
        ];
    }


    /**
    * Partial update for existing page
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

        if (ark($input, 'domains_id', false) !== false) {

            // Check domain id, if it is in patch data
            $domain = $this->getDomain($input['domains_id']);

            if ($domain->id === 0) {

                return [
                    'error' => "Specified domain does not exist",
                ];
            }
        }

        if (ark($input, 'navs_id', false) !== false) {

            // Check nav id, if it is in patch data
            $nav = $this->getNav($input['navs_id']);

            if ($nav->id === 0) {

                return [
                    'error' => "Specified nav does not exist",
                ];
            }
        }

        if (ark($input, 'slug', false) !== false) {

            // Check same page slug and exclude updated id, if it is in patch data
            $exists = $this->exists(' slug LIKE ? AND domains_id = ? AND id != ? ', [$input['slug'], $input['domains_id'], $id]);

            if ($exists === true) {

                return [
                    'error' => "Page with slug {$input['slug']} already exists",
                ];
            }
        }

        $input = $this->extend($input, 'update');

        return [
            'patched' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Hard & soft delete existing page
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


    protected function getNav($id)
    {
        $temporaryType = $this->assets->swapType('navs');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }


    protected function getDomain($id)
    {
        $temporaryType = $this->assets->swapType('domains');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }
}