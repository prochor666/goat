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
    * Check if asset exists, return empty asset if not, PDO syntax
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

            $test_value = is_array($inputPropertyValue) ? $inputPropertyValue: (string)$inputPropertyValue;

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


    /**
    * VAlidation messages getter
    * @param void
    * @return array
    */
    public function getValidationMessages(): array
    {
        return $this->messages;
    }


    /**
    * Check if value is empty or zero length
    * @param mixed $value
    * @return bool
    */
    protected function isEmptyValue($value): bool
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

        return $input;
    }


    /**
    * Convert data object
    * @param object|array $data
    * @param bool $forceAssoc
    * @return iterable
    */
    public function dbSafeConvert($data, $forceAssoc = true): iterable
    {
        foreach ($data as $key => $value) {

            if (is_object($data)) {

                $data->$key = $this->isJSONIterable($value) ? $this->JSONExtract($value, $forceAssoc): $value;
            } else {

                $data[$key] = $this->isJSONIterable($value) ? $this->JSONExtract($value, $forceAssoc): $value;
            }
        }

        return $data;
    }


    /**
    * Extract JSON value as array or object
    * @param string $data
    * @param bool $forceAssoc
    * @return iterable
    */
    public function JSONExtract($value, $forceAssoc = true): iterable
    {
        return json_decode($value, $forceAssoc);
    }


    /**
    * Detect if JSON value is object or array
    * @param string $value
    * @param bool $forceAssoc
    * @return bool
    */
    public function isJSONIterable($value): bool
    {
        if (!is_string($value)) {

            return false;
        }

        if (strlen($value) < 2) {

            return false;
        }

        // Any needle JSON string has to be wrapped in {} or [].
        if ('{' != $value[0] && '[' != $value[0]) {

            return false;
        }

        // Verify that the trailing character matches the first character.
        $lastChar = $value[strlen($value) -1];

        if ('{' == $value[0] && '}' != $lastChar) {

            return false;
        }

        if ('[' == $value[0] && ']' != $lastChar) {

            return false;
        }

        return true;
    }


    /**
    * Convert array indexes (which are array or object) to JSON strings
    * @param array $input
    * @return array
    */
    public function dbSafe($input): array
    {
        foreach ($input as $key => $value) {

            $needle = $this->needConversion($value);

            switch ($needle) {

                case 'to_json':
                    $input[$key] = $this->toDbJSON($value);
                    break;

                case 'to_empty_string':
                    $input[$key] = '';
                    break;

                case 'bool_to_int':
                    $input[$key] = $value ? 1: 0;
                    break;

                default:
                    $input[$key] = $value;
            }
        }

        return $input;
    }



    /**
    * Check if value needs conversion to JSON string or needs to be fixed (resource|bool)
    * @param mixed $value
    * @return string
    */
    public function needConversion($value): string
    {
        if (is_array($value) || is_object($value)) {

            return 'to_json';
        }

        if (is_null($value) || is_resource($value)) {

            return 'to_empty_string';
        }

        if (is_bool($value)) {

            return 'bool_to_int';
        }

        return '';
    }


    /**
    * Convert array or object to JSON string
    * @param array $input
    * @return string
    */
    public function toDbJSON($value): string
    {
        return json_encode($value);
    }


    /**
    * Convert array or object to JSON string
    * @param int $id
    * @param string $target
    * @param array $item
    * @return array
    */
    protected function storeMetaValue($id, $tag, $target, $item): array
    {
        $temporaryType = $this->assets->swapType('metavalues');

        $options = [];

        // Check meta record
        $metaExists = $this->existsWithData(' targetid = ? AND tag LIKE ? AND target LIKE ? ', [$item['id'], $tag, $target]);

        $item['realvalue']['value'] = call_user_func_array(
            [$this, $item['validate_as']],
            [$item['realvalue']['value'], $options]
        ) ? $item['realvalue']['value']: false;

        if ($item['realvalue']['value'] === false) {

            return $item;
        }

        if ($metaExists->id > 0) {

            $this->assets->update($metaExists->id, $item);
        } else {

            $this->assets->create($item);
        }

        $this->assets->setType($temporaryType);

        return $item;
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
