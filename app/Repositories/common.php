<?php

    function abort ($code = null) {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        die();
    }

    function respond ($result) {
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                echo '<pre>', print_r($result), '</pre>';
                die();
                break;
            case "POST":
                echo json_encode($result);
                break;
        }
    }