<?php
    session_start();

    header('Content-Type: application/force-download');

    header('Content-Length:' . filesize($_SESSION['next-download']['location']));
    header("Content-Disposition: inline; filename=\"".$_SESSION['next-download']['name']."\"");

    $filePointer = fopen($_SESSION['next-download']['location'],"rb");
    fpassthru($filePointer);

    unset($_SESSION['next-download']);

    echo '<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>';
    echo '<script>$(document).ready(function() {setTimeout(function() {parent.closeFrame(' . $_SESSION['next-download']['name'] . ');},120000)})</script>';