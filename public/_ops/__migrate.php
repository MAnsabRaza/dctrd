<?php
    include('auth.php');

	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
    
    auth_callback(function() {
        echo '<pre>';
		echo `cd ../.. && ls -al`;
        echo `cd ../.. && /usr/lib/x86_64-linux-gnu/php artisan migrate --force`;
    });
?>