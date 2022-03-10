<?php
namespace GoatCore\Traits;

trait Validator
{
    /**
    * Cast && Validate int value, min and max range optional validation
    * @param mixed $value
    * @param array $options
    * @return bool
    */
    public function int($value, $options = []): bool
    {
        $min = ark($options, 'min', false);
        $max = ark($options, 'max', false);

        if(is_int($value)) {

            if (is_int($min) && $value < $min) {

                return false;
            }

            if (is_int($max) && $value > $max) {

                return false;
            }

            return true;
        }

        return false;
    }


    /**
    * Cast && Validate float value, min and max range optional validation
    * @param mixed $value
    * @param array $options
    * @return bool
    */
    public function float($value, $options = []): bool
    {
        $min = ark($options, 'min', false);
        $max = ark($options, 'max', false);

        if(is_float($value)) {

            if (is_float($min) && $value < $min) {

                return false;
            }

            if (is_float($max) && $value > $max) {

                return false;
            }

            return true;
        }

        return false;
    }


    /**
    * Cast && Validate numeric value, min and max range optional validation
    * @param mixed $value
    * @return bool
    */
    public function numeric($value): bool
    {
        return is_numeric($value) ? true: false;
    }


    /**
    * Validate domain value
    * @param mixed $value
    * @return bool
    */
    public function domain($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }


    /**
    * Validate domain value
    * @param mixed $value
    * @return bool
    */
    public function email($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
    }


    /**
    * Validate URL(not URN) value
    * @param mixed $value
    * @return bool
    */
    public function url($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_URL);
    }


    /**
    * Validate IPv4/IPv6 value
    * @param mixed $value
    * @return bool
    */
    public function ip($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_IP);
    }


    /**
    * Validate MAC address value
    * @param mixed $value
    * @return bool
    */
    public function mac($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_MAC);
    }


    /**
    * Validate boolean value
    * @param mixed $value
    * @return bool
    */
    public function bool($value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }


    /**
    * Validate array value, optional sub validator for values
    * Empty array is valid
    * @param mixed $value
    * @param array $subvalidator
    * @return bool
    */
    public function arrayOf($value, $options = []): bool
    {
        if (!is_array($value)) {

            return false;
        }

        $validation_method = ark($options, 'validation_method', false);

        if ($validation_method !== false) {

            foreach($value as $v) {

                if (!is_array($options) && call_user_func_array([$this, $validation_method], [$v]) === false) {

                    return false;

                } elseif (call_user_func_array([$this, $validation_method], [$v, $options]) === false) {

                    return false;
                }
            }
        }

        return true;
    }


    /**
    * Validate string value, min and max length optional validation
    * @param mixed $value
    * @param array $options
    * @return bool
    */
    public function string($value, $options = []): bool
    {
        $min = ark($options, 'min', 0);
        $max = ark($options, 'max', 0);

        if (is_string($value)) {

            if (is_int($min) && $min > 0 && mb_strlen($value) < $min) {

                return false;
            }

            if (is_int($max) && $max > $min && mb_strlen($value) > $max) {

                return false;
            }

            return true;
        }

        return false;
    }


    /**
    * Validate lang alpha2 code value
    * As the options, the ISO-639 array is required
    * JSON sample:
    * [{
    *     "name": "English",
    *     "alpha2": "en",
    *     "alpha3-b": "eng"
    * }]
    * @param mixed $value
    * @param array $options
    * @return bool
    */
    public function lang($value, $options = []): bool
    {
        $langs = ark($options, 'langs', []);

        if (array_search($value, array_column($langs, 'alpha2')) !== false) {

           return true;
        }

        return false;
    }


    /**
    * Validate string password value, optional security level and minimal password length
    * If password meets all requirements, 0 is returned, 0 means ok
    * Otherwise, the first unmet security level is returned
    * There is an option $occurs, which means how many current characters are required
    * @param mixed $value
    * @param array $options
    * @return bool
    */
    public function password($value, $options = []): bool
    {
        $level = (int)ark($options, 'level', 3);
        $min = (int)ark($options, 'min', 6);
        $occurs = (array)ark($options, 'occurs', [
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 1,
        ]);

        /*
        | eReg explained + levels:
        | FULL expression: $\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$
        | $ = beginning of string
        | \S* = any set of characters
        | (?=\S{6,}) = of at least length 6
        | (?=\S*[a-z]) = containing at least one lowercase letter
        | (?=\S*[A-Z]) = and at least one uppercase letter
        | (?=\S*[\d]) = and at least one number
        | (?=\S*[\W]) = and at least a special character (non-word characters)
        | $ = end of the string
        */
        $ereg = '';

        if ((int)$level > 5 || (int)$level < 1) {

            $level = 3;
        }

        for ($i = 1; $i <= $level; $i++) {

            switch ($i) {

                case 1: // require only specified length aka PIN

                    $ereg .= '(?=\S{'.$min.',})';
                    break;
                case 2: // +require lowercase letter

                    $ereg .= '(?='.str_repeat('\S*[a-z]', (int)ark($occurs, 2, 1)).')';
                    break;
                case 3: // +require number

                    $ereg .= '(?='.str_repeat('\S*[\d]', (int)ark($occurs, 3, 1)).')';
                    break;
                case 4: // +require uppercase letter

                    $ereg .= '(?='.str_repeat('\S*[A-Z]', (int)ark($occurs, 4, 1)).')';
                    break;
                default: // 5 +require special character (non-word characters)

                    $ereg .= '(?='.str_repeat('\S*[\W]', (int)ark($occurs, 5, 1)).')';
            }

            if (!preg_match_all( '$\S*'.$ereg.'\S*$', $value)) {
                return false;
            }
        }

        return true;
    }

}
