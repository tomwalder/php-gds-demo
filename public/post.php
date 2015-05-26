<?php
/**
 * Record an entry in the Guest Book
 *
 * @author Tom Walder <tom@docnet.nu>
 */
require_once('../vendor/autoload.php');
require_once('../config.php');

// Filter vars
$str_name = substr(filter_input(INPUT_POST, 'guest-name', FILTER_SANITIZE_STRING), 0, 30);
$str_message = substr(filter_input(INPUT_POST, 'guest-message', FILTER_SANITIZE_STRING), 0, 1000);

// VERY crude anti-spam-bot check
if(\GDS\Demo\Spammy::anyLookSpammy([$str_name, $str_message])) {
    syslog(LOG_WARNING, 'Skipping potential spam from [' . $_SERVER['REMOTE_ADDR'] . ']: ' . print_r($_POST, TRUE));
    header("Location: /?spam=maybe");
} else {
    $obj_repo = new \GDS\Demo\Repository();
    $obj_repo->createPost($str_name, $str_message);
    header("Location: /");
}
