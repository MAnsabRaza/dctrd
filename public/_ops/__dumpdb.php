<?php
    include('auth.php');
    
    auth_callback(function() {
        $dbhost = 'localhost';
        $dbuser = 'koamtravel_user';
        $dbpass = 'HKA99rLiQQvgBxV';
        $dbname = 'koamtravel_koam';

        exec("mysqldump --opt -h $dbhost -u$dbuser -p$dbpass $dbname", $out);

        header('Content-Type: application/text');
        header("Content-disposition: attachment; filename=\"" . $dbname .'_'. date('Ymd') .'.sql' . "\"");
        echo implode("\n", $out);
    });
?>