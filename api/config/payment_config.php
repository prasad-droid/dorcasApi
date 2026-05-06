<?php
// CCAvenue Configuration
define('CCAV_MERCHANT_ID', '4411874');
define('CCAV_WORKING_KEY', '91C1EF3FC5070D0CD1C27706261970DD');
define('CCAV_ACCESS_CODE', 'AVNV85MK95BN05VNNB');

// Environment URLs
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    define('BASE_URL', 'http://localhost/dorcasApi');
    define('FRONTEND_URL', 'http://localhost:3000');
} else {
    define('BASE_URL', 'https://dorcasaid.com/api');
    define('FRONTEND_URL', 'https://dorcasaid.com'); // Update with actual live domain
}

define('CCAV_REDIRECT_URL', BASE_URL . '/api/payments/ccavenue_handler.php');
define('CCAV_CANCEL_URL', BASE_URL . '/api/payments/ccavenue_handler.php');
?>
