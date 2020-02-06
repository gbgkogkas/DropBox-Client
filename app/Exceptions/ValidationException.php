<?php namespace App\Exceptions;


class ValidationException extends BasicException {

    function __construct($data)
    {
        parent::__construct($data);
    }
}