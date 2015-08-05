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

$str_as = (string)base_convert(substr(filter_input(INPUT_POST, 'guest-as', FILTER_SANITIZE_STRING), 0, 20), 36, 10);
if(!in_array($str_as, [date('YmdH'), date('YmdH', strtotime('-1 hour'))])) {
    syslog(LOG_WARNING, 'Skipping potential AV spam from [' . $_SERVER['REMOTE_ADDR'] . ']: ' . print_r($_POST, TRUE));
    header("Location: /?spam=maybe");
    exit();
}

use \GDS\Demo\Spammy;
use \GDS\Demo\Repository;

// VERY crude anti-spam-bot check
if(Spammy::anyLookSpammy([$str_name, $str_message])) {
    syslog(LOG_WARNING, 'Skipping potential spam from [' . $_SERVER['REMOTE_ADDR'] . ']: ' . print_r($_POST, TRUE));
    header("Location: /?spam=maybe");
} else {
    syslog(LOG_DEBUG, 'Proceeding... ' . print_r($_SERVER, TRUE) . "\n\n" . print_r($_POST, TRUE));
    $obj_repo = new Repository();
    $obj_repo->createPost($str_name, $str_message, $_SERVER['REMOTE_ADDR']);
    header("Location: /");
}
