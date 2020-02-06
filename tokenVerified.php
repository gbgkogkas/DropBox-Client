<?php

session_start();

$_SESSION['dropbox-code'] = $_GET['code'];

header("Location: http://localhost/dropbox/");