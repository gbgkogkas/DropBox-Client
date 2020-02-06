<?php
require_once 'vendor/autoload.php';

use App\Repositories\Validator\FilesValidator;


    class dropBoxClient {

        public static $redirect = 'http://localhost/dropbox/tokenVerified.php';

        private $key = 's8fn9zrw29dcfk6';
        private $secret = '7o127aqilz0ly5u';

        public function __construct(){
            if (!isset($_SESSION)) {
                session_start();
            }

            if (! array_key_exists('dropbox-account', $_SESSION)) {
                $this->validateUserCode();
            } else {
                $this->validateUserAccessToken();
            }
        }


        /**
         * User validation
         */

        /**
         * Validate user`s code
         */
        public function validateUserCode () {
            $fields = [
                'code'          => $_SESSION['dropbox-code'],
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => self::$redirect,
            ];
            
            $headers = array(
                "Authorization: Basic " . base64_encode($this->key . ":" . $this->secret),
                "Content-Type: application/x-www-form-urlencoded",
            );

            $output = $this->post("https://api.dropboxapi.com/oauth2/token", $headers, $fields);

            if (array_key_exists('error_description', $output)) {
                if (strpos($output['error_description'], "code doesn't exist or has expired") !== false) {
                    header("Location: https://www.dropbox.com/oauth2/authorize?client_id=s8fn9zrw29dcfk6&response_type=code&redirect_uri=http://localhost/dropbox/tokenVerified.php");
                }
            } else {
                if (array_key_exists('access_token', $output)) {
                    $_SESSION['dropbox-access-token'] = $output['access_token'];
                    $this->validateUserAccessToken();
                }
            }
        }

        /**
         * Validate user`s token and account
         */
        public function validateUserAccessToken () {
            $headers = array(
                "Authorization: Bearer " . $_SESSION['dropbox-access-token'],
                "Content-Type: application/json"
            );

            $output = $this->post("https://api.dropboxapi.com/2/users/get_current_account", $headers);

            if (! empty($output) && ! is_null($output)) {
                $_SESSION['dropbox-account'] = $output;
            }
        }

        public function list_folder ($input) {
            list($result, $errors) = FilesValidator::listFolderValidator($input);

            if (! $result) {
                throw new \App\Exceptions\ValidationException($errors);
            }

            $fields = [
                "path"                                  => $this->normalizePath($input['path']),
                "recursive"                             => false,
                "include_media_info"                    => true,
                "include_deleted"                       => false,
                "include_has_explicit_shared_members"   => false,
                "include_mounted_folders"               => true,
                "include_non_downloadable_files"        => true
            ];

            $headers = array(
                "Authorization: Bearer " . $_SESSION['dropbox-access-token'],
                "Content-Type: application/json",
            );

            $output = $this->post("https://api.dropboxapi.com/2/files/list_folder", $headers, $fields, true);

            return $output;
        }

        public function delete_file ($input) {
            list($result, $errors) = FilesValidator::deleteFileValidator($input);

            if (! $result) {
                throw new \App\Exceptions\ValidationException($errors);
            }

            $headers =  array(
                'Authorization: Bearer ' . $_SESSION['dropbox-access-token'],
                'Content-Type: application/json',
            );

            $fields = array(
                'path' => $input['path']
            );

            $this->post("https://api.dropboxapi.com/2/files/delete_v2", $headers, $fields, true);

            $output = $this->list_folder(array('path' => $input['source_path']));

            return $output;
        }

        public function download_file ($input) {
            list($result, $errors) = FilesValidator::downloadFileValidator($input);

            if (! $result) {
                throw new \App\Exceptions\ValidationException($errors);
            }

            $out_filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $input['name'];

            $headers = array(
                'Authorization: Bearer ' . $_SESSION['dropbox-access-token'],
                'Content-Type:',
                'Dropbox-API-Arg: {"path":"' . $input['path'] . '"}'
            );

            $this->post("https://content.dropboxapi.com/2/files/download", $headers, null, false, $out_filepath);

            $_SESSION['next-download'] = array(
                'location' => $out_filepath,
                'name'  => $input['name']
            );
        }

        public function upload($input) {
            list($result, $errors) = FilesValidator::uploadFileValidator($input);

            if (! $result) {
                throw new \App\Exceptions\ValidationException($errors);
            }

            $headers =  array(
                'Authorization: Bearer ' . $_SESSION['dropbox-access-token'],
                'Content-Type: application/octet-stream',
                'Dropbox-API-Arg: {"path":"' . $this->normalizePath($input['path'] . $_FILES['file']['name']) . '", "mode":"add"}'
            );

            $this->put('https://content.dropboxapi.com/2/files/upload', $headers, $_FILES['file']);

            $output = $this->list_folder(['path' => $input['path']]);

            return $output;
        }


        /**
         * Requests exchange functions
         */

        /**
         * Post request to DropBox API
         * @param $url
         * @param $headers
         * @param array $fields
         * @param bool $sendJson
         * @return bool|mixed|string
         */
        private function post ($url, $headers, $fields = array(), $sendJson = false, $filePath = null) {
            if (! empty($fields)) {
                if ($sendJson) {
                    $fields_string = json_encode($fields);
                } else {
                    $fields_string = '';
                    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
                    rtrim($fields_string, '&');
                }
            }


            $options = array(
                CURLOPT_URL             =>  $url,
                CURLOPT_HTTPHEADER      =>  $headers,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_POST            => true,
                CURLOPT_VERBOSE         => true
            );

            if (isset($fields_string)) {
                $options[CURLOPT_POSTFIELDS] = $fields_string;
            }

            $ch = curl_init();
            curl_setopt_array($ch, $options);

            if (! is_null($filePath)) {
                $out_fp = fopen($filePath, 'w+');

                if ($out_fp === FALSE) {
                    echo "fopen error; can't open $filePath\n";
                    return (NULL);
                }

                curl_setopt($ch, CURLOPT_FILE, $out_fp);

                $metadata = null;
                curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$metadata) {
                    $prefix = 'dropbox-api-result:';
                    if (strtolower(substr($header, 0, strlen($prefix))) === $prefix) {
                        $metadata = json_decode(substr($header, strlen($prefix)), true);
                    }
                    return strlen($header);
                });
            }

            $output = curl_exec($ch);

            curl_close($ch);

            if (! is_null($filePath)) {
                fclose($out_fp);
            }

            $output = json_decode($output, true);

            return $output;
        }

        private function put ($url, $headers, $file) {
            $fp = fopen($file['tmp_name'], 'rb');

            $options = array(
                CURLOPT_HTTPHEADER      =>  $headers,
                CURLOPT_PUT             => true,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_INFILE          => $fp,
                CURLOPT_INFILESIZE      => $file['size'],
                CURLOPT_RETURNTRANSFER  => true,
            );

            $ch = curl_init($url);
            curl_setopt_array($ch, $options);

            $output = curl_exec($ch);

            curl_close($ch);
            fclose($fp);

            return $output;
        }

        /**
         * Normalize folder/file path
         * @param string $path
         * @return string
         */
        protected function normalizePath(string $path) {
            if (preg_match("/^id:.*|^rev:.*|^(ns:[0-9]+(\/.*)?)/", $path) === 1) {
                return $path;
            }

            $path = trim($path, '/');

            return ($path === '') ? '' : '/'.$path;
        }
    }