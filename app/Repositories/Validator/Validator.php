<?php namespace App\Repositories\Validator;


class Validator
{
    function __construct() {}

    protected static function validate ($input, $rules) {
        $errors = [];

        foreach ($rules as $key => $properties) {
            $errors = self::executeRules($input, $key, $properties, $errors);
        }

        return [(count($errors) ? false : true), $errors];
    }

    private static function executeRules ($input, $key, $properties, $errors) {
        foreach ($properties as $property) {
            if (method_exists(__CLASS__, $property)) {
                list($result, $error) = self::{$property}($input, $key);

                if (! $result) {
                    $errors[$key] = $error;

                    break;
                }
            } else {
                $errors[$key] = 'Method missing';

                break;
            }
        }

        return $errors;
    }

    private static function required ($input, $key) {
        $result = array_key_exists($key, $input);

        return [$result, ($result ? '' : 'Field is required')];
    }
}