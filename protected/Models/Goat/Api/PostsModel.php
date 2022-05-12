<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* PostsModel - Posts API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class PostsModel extends BasicAssetModel
{
    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'title' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 0, 'max' => 255],
            ],
            'slug' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 0, 'max' => 255],
            ],
            'annotation' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 0, 'max' => 550],
            ],
            'content' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => false,
            ],
            'image' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => false,
            ],
            'order' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => false,
            ],
            'pages_id' => [
                'validation_method'    => 'int',
                'default'              => 1,
                'options'              => ['min' => 1],
            ],
            'public' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 1],
            ],
            'date_publish' => [
                'validation_method'    => 'string',
                'default'              => $this->assets->isoDateTime(),
                'options'              => false,
            ]
        ];
    }


    /**
    * Create new post
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

        // Check page id
        $page = $this->getPage($input['pages_id']);

        if ($page->id === 0) {

            return [
                'error' => "Specified page does not exist",
            ];
        }

        // Create slug
        $input['slug'] = urlSafe( mb_strlen($input['title']) > 0 ? $input['title']: rnd(17) );

        // Check same post slug
/*         if ($this->exists(' slug LIKE ? AND pages_id = ? ', [$input['slug'], $input['pages_id']]) !== false) {

            return [
                'error' => "Post with slug {$input['slug']} already exists",
            ];
        } */

        $input = $this->extend($input, 'create');
        $created = $this->assets->oneToMany($page, $this->assets->getType(), [$input]);

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
    * Full update existing post
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
        $page = $this->getPage($input['pages_id']);

        if ($page->id === 0) {

            return [
                'error' => "Specified page does not exist",
            ];
        }

        // Create slug
        $input['slug'] = urlSafe( mb_strlen($input['title']) > 0 ? $input['title']: rnd(17) );

        // Check same post slug and exclude updated id
        $exists = $this->exists(' slug LIKE ? AND pages_id = ? AND id != ? ', [$input['slug'], $input['pages_id'], $id]);

        if ($exists === true) {

            return [
                'error' => "Post with slug {$input['slug']} already exists",
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
    * Partial update for existing post
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

        if (ark($input, 'pages_id', false) !== false) {

            // Check page id, if it is in patch data
            $page = $this->getPage($input['pages_id']);

            if ($page->id === 0) {

                return [
                    'error' => "Specified page does not exist",
                ];
            }
        }

         if (ark($input, 'slug', false) !== false) {

            // Check same post slug and exclude updated id, if it is in patch data
            $exists = $this->exists(' slug LIKE ? AND pages_id = ? AND id != ? ', [$input['slug'], $input['pages_id'], $id]);

            if ($exists === true) {

                return [
                    'error' => "Post with slug {$input['slug']} already exists",
                ];
            }
        }

        $input = $this->extend($input, 'update');

        return [
            'patched' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Hard & soft delete existing post
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


    protected function getPage($id)
    {
        $temporaryType = $this->assets->swapType('pages');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }
}