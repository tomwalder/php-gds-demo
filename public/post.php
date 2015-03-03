<?php
/**
 * Record an entry in the Guest Book
 *
 * @author Tom Walder <tom@docnet.nu>
 */
require_once('../vendor/autoload.php');
require_once('../config.php');

// Client & Gateway
$obj_google_client = GDS\Gateway::createGoogleClient('php-gds-demo', GDS_ACCOUNT, GDS_KEY_FILE);
$obj_gateway = new GDS\Gateway($obj_google_client, 'php-gds-demo');

// Define our schema - the posted datetime as an indexed field
$obj_schema = (new GDS\Schema('Guestbook'))
    ->addDatetime('posted')
    ->addString('name', FALSE)
    ->addString('message', FALSE);

// And the store
$obj_store = new GDS\Store($obj_gateway, $obj_schema);

// Insert the entity (plus limit the data to the same values as the form)
$obj_store->upsert($obj_store->createEntity([
    'posted' => date('Y-m-d H:i:s'),
    'name' => substr($_POST['guest-name'], 0, 30),
    'message' => substr($_POST['guest-message'], 0, 1000),
]));

header("Location: /");