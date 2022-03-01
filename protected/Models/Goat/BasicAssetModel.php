<?php
namespace Goat;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;

/**
* BasicAssetModel - Basic db asset model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class BasicAssetModel
{
    protected $app;

    protected $assets;

    protected $predefined;

    use \GoatCore\Traits\Validator;
    use \Goat\History;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        $this->app = $app;
        $this->assets = $assets;
        $this->predefined = [];
    }


    /**
    * Get one record from collection
    * @param int $id
    * @return object
    */
    public function one($id): object
    {
        return $this->assets->one($id);
    }


    /**
    * Find assets by criteria
    * @param array $input
    * @return array
    */
    public function find($input): array
    {
        return $this->assets->find($input);
    }


    /**
    * Check if asset exists, return asset, PDO syntax
    * @param string $cols
    * @param array $values
    * @return object
    */
    protected function existsWithData($cols, $values): object
    {
        return $this->assets->findOneWithDefault($cols, $values);
    }


    /**
    * Check if asset exists, PDO syntax
    * @param string $cols
    * @param array $values
    * @return bool
    */
    protected function exists($cols, $values): bool
    {
        $found = $this->assets->findOne($cols, $values);
        return is_null($found) ? false: true;
    }


    /**
    * Get one to many relation
    * @param object $relation
    * @param string $prop
    * @return object
    */
    public function related($relation, $prop): object
    {
        $this->assets->getRelated($relation, $prop);
        return $this->assets->clearProp($relation, $prop);
    }


    /**
    * Search for false value, which is targeting invalidated property
    * @param array $input
    * @return bool
    */
    protected function invalid($input): bool
    {
        return array_search(false, $input, true);
    }


    /**
    * Normalize input data array, by internal fixed property
    * Fullfill input array with default values, when $setDefaults is set to true
    * @param array $input
    * @param bool $setDefaults
    * @return array
    */
    protected function normalize($input, $setDefaults = false): array
    {
        foreach($this->predefined as $prop => $setup) {

            $inputProp = ark($input, $prop, false);

            if ($setDefaults === true && $inputProp === false) {

                $inputProp = $setup['default'];
            }

            if ($inputProp !== false) {

                if (!isset($setup['options']) || $setup['options'] === false) {

                    $input[$prop] = call_user_func_array([$this, $setup['validation_method']], [$inputProp]) ? $inputProp: false;
                } else {

                    $input[$prop] = call_user_func_array([$this, $setup['validation_method']], [$inputProp, $setup['options']]) ? $inputProp: false;
                }
            }
        }

        return $input;
    }


    /**
    * Extend or update input with unified creator, updater, delete info
    * @param array $input
    * @param string $type
    * @return array
    */
    protected function extend($input, $type = 'create'): array
    {
        if ($type === 'create') {

            return array_merge($input, [
                'created_at' => $this->assets->isoDateTime(),
            ]);
        }

        if ($type === 'update') {

            return $input;
        }

        if ($type === 'delete') {

            return array_merge($input, [
                'deleted_at' => $this->assets->isoDateTime(),
            ]);
        }

        return [];
    }


    /**
    * Save query history log
    * @param array $data
    * @return int
    */
    protected function history($data): int
    {
        $temporaryType = $this->assets->swapType('history');
        $result = $this->assets->create($this->record($data));
        $this->assets->setType($temporaryType);
        return $result;
    }
}
