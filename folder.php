<?php
    include('app/Repositories/common.php');
    include('app/DropBoxClient.php');

    if (!array_key_exists('action', $_REQUEST)) {
        abort();
    } else {
        try {
            $result = executeAction();
        } catch (\App\Exceptions\BasicException $e) {
            $result = $e->toArray();

            $result['success'] = false;
        }

        respond($result);
    }

    function executeAction () {
        $action = $_REQUEST['action'];
        $result = array('success' => true);

        $dropBoxClient = new dropBoxClient();

        switch ($action) {
            case 'open':
                $result['data'] = $dropBoxClient->list_folder($_REQUEST);
                break;
            default:
                abort();
        }

        return $result;
    }

