<?php

function auth_callback($callback) {
    // echo '<pre>';var_dump($_SERVER);exit;
    $auth = '';
    $username = 'admin';
    $password = 'adm1n@';
    
    if( isset($_SERVER["HTTP_AUTHORIZATION"]) && !empty($_SERVER["HTTP_AUTHORIZATION"]) ) {
        $auth = base64_decode( substr($_SERVER["HTTP_AUTHORIZATION"],6)) ;
        list($auth_name, $auth_pass) = explode(':', $auth);
    }
    
    if ( (strlen($auth) == 0) || ( strcasecmp($auth, ":" )  == 0 ) ||
        ! (isset($auth_name) && isset($auth_pass) && $auth_name == $username && $auth_pass == $password) )
    {
        //echo('<pre>');var_dump($_SERVER);exit;
        header( 'WWW-Authenticate: Basic realm="Private"' );
        header( 'HTTP/1.0 401 Unauthorized' );
    }
    else
    {
        $callback();
    }
}
