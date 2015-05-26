<?php
/**
 * Record an entry in the Guest Book
 *
 * @author Tom Walder <tom@docnet.nu>
 */
require_once('../vendor/autoload.php');
require_once('../config.php');

// VERY crude anti-spam-bot check
if(\GDS\Demo\Spammy::looksSpammy($_POST['guest-name']) || \GDS\Demo\Spammy::looksSpammy($_POST['guest-message'])) {
    syslog(LOG_WARNING, 'Skipping potential spam: ' . print_r($_POST, TRUE));
    header("Location: /?spam=maybe");
} else {
    $obj_repo = new \GDS\Demo\Repository();
    $obj_repo->createPost($_POST['guest-name'], $_POST['guest-message']);
    header("Location: /");
}
