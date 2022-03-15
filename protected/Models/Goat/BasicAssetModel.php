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

    protected $messages;

    use \GoatCore\Traits\Validator;
    use \Goat\History;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        $this->app = $app;
        $this->assets = $assets;
        $this->predefined = [];
        $this->messages = [];
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

            $inputPropertyValue = ark($input, $prop, false);
            $required = ark($setup, 'required', false);
            $default = ark($setup, 'default', false);
            $validationMethod = ark($setup, 'validation_method', false);
            $options = ark($setup, 'options', false);

            if ($setDefaults === true && $inputPropertyValue === false && $default !== false) {

                // Set default value
                $inputPropertyValue = $default;
            }

            $validate = false;


            // Allow empty value, when required is false
            if ($required === false && $inputPropertyValue !== false && is_callable([$this, $validationMethod]) && $this->isEmptyValue($inputPropertyValue) === false) {

                $validate = true;
            }

            if ($required === true && $inputPropertyValue !== false && is_callable([$this, $validationMethod])) {

                $validate = true;
            }


            if ($validate === true) {

                if ($options === false) {

                    $this->messages[$prop] = ['Validation without options', $inputPropertyValue];

                    // Without options
                    $input[$prop] = call_user_func_array(
                        [$this, $validationMethod],
                        [$inputPropertyValue]
                    ) ? $inputPropertyValue: false;

                } else {

                    $this->messages[$prop] = ['Validation with options', $inputPropertyValue, $options];

                    // With options
                    $input[$prop] = call_user_func_array(
                        [$this, $validationMethod],
                        [$inputPropertyValue, $options]
                    ) ? $inputPropertyValue: false;
                }


            } else {

                $this->messages[$prop] = ['No validation', $inputPropertyValue];
                $input[$prop] = $inputPropertyValue;
            }
        }

        return $input;
    }


    public function getValidationMessages()
    {
        return $this->messages;
    }


    protected function isEmptyValue($value)
    {
        if (is_array($value) && count($value) === 0) {

            return true;
        }

        if (is_scalar($value) && !is_bool($value) && mb_strlen((string)$value) === 0) {

            return true;
        }

        return false;
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
