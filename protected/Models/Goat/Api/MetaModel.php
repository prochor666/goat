<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* MetaModel - Meta data API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class MetaModel extends BasicAssetModel
{
    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'tag' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1, 'max' => 255],
            ],
            'name' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1, 'max' => 255],
            ],
            'type' => [
                'validation_method'    => 'string',
                'default'              => 0,
                'options'              => ['min' => 0, 'max' => 17],
            ],
            'settings' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => false,
            ],
            'order' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => false,
            ],
            'domains_id' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 1],
            ],
        ];
    }


    /**
    * Create new nav
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

        // Check same nav name
        if ($this->exists(' tag LIKE ? AND domains_id = ? ', [$input['tag'], $input['domains_id']]) === true) {

            return [
                'error' => "Meta with tag {$input['tag']} already exists",
            ];
        }

        $input = $this->extend($input, 'create');
        $created = $this->assets->oneToMany($domain, $this->assets->getType(), [$input]);

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
    * Full update existing nav
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

        // Check same nav name and exclude updated id
        $exists = $this->exists(' tag LIKE ? AND id != ? ', [$input['tag'], $id]);

        if ($exists === true) {

            return [
                'error' => "Meta with tag {$input['tag']} already exists",
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
    * Partial update for existing nav
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

            // Check domain id
            $domain = $this->getDomain($input['domains_id']);

            if ($domain->id === 0) {

                return [
                    'error' => "Specified domain does not exist",
                ];
            }
        }

        if (ark($input, 'name', false) !== false) {

            // Check same nav name and exclude updated id
            $exists = $this->exists(' tag LIKE ? AND id != ? ', [$input['tag'], $id]);

            if ($exists === true) {

                return [
                    'error' => "Meta with tag {$input['tag']} already exists",
                ];
            }
        }

        $input = $this->extend($input, 'update');

        return [
            'patched' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Hard & soft delete existing nav
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


    protected function getDomain($id)
    {
        $temporaryType = $this->assets->swapType('domains');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }
}