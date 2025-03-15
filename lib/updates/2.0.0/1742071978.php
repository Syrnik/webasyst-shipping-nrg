<?php
$_dir = wa()->getConfig()->getPath('plugins') . '/shipping/nrg/lib';

$files = [
    '/classes/EstimatedDelivery.class.php',
    '/vendors/serger/cake-utility',
];

foreach ($files as $file) {
    try {
        waFiles::delete($_dir . $file);
    } catch (Exception $e) {
        waLog::log('NRG shipping plugin update (' . __FILE__ . "): Error deleting old vendor file $file: " . $e->getMessage());
    }
}
