<?php namespace App\Exceptions;


class BasicException extends \Exception {

    private $data;
    private $msg = '';

    function __construct ($data) {
        $this->data = $data;
    }

    public function toArray () {
        $array = [
            'data' => $this->data,
            'msg' => $this->msg
        ];

        return $array;
    }
}