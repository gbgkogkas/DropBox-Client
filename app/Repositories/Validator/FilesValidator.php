<?php namespace App\Repositories\Validator;


class FilesValidator extends Validator {

    private static $rules = [];

    public static function listFolderValidator ($input) {
        self::$rules['path'] = [
            'required'
        ];

        return self::validate($input, self::$rules);
    }

    public static function deleteFileValidator ($input) {
        self::$rules = array(
            'path' => 'required',
            'source_path' => 'required',
        );

        return self::validate($input, self::$rules);
    }

    public static function downloadFileValidator ($input) {
        self::$rules = array(
            'path' => 'required',
            'name' => 'required',
        );

        return self::validate($input, self::$rules);
    }

    public static function uploadFileValidator ($input) {
        self::$rules = array(
            'path' => 'required',
            'file' => 'required'
        );

        $input = array_merge($input, $_FILES);

        return self::validate($input, self::$rules);
    }
}