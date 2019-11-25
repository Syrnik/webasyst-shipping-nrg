<?php
$vendor_dir = wa()->getConfig()->getPath('plugins') . '/shipping/nrg/lib/vendors';

$files = [
    '/webit'
];

foreach ($files as $file) {
    try {
        waFiles::delete($vendor_dir . $file);
    } catch (Exception $e) {
        waLog::log('NRG shipping plugin update (' . __FILE__ . "): Error deleting old vendor file $file: " . $e->getMessage());
    }
}
