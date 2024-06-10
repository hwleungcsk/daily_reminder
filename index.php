<?php

    // routes
    // / - home
    // /@name/toggle
    // POST /
    // /@name/delete
    // /@name/date/@date

    // set timezone to hk
    date_default_timezone_set('Asia/Hong_Kong');

    // fatfree framework
    require 'vendor/autoload.php';

    $f3 = \Base::instance();

    // start session
    session_start();

    // route /
    $f3->route('GET /', function($f3) {
        // if session $msg is set, set $msg
        $f3->set('msg', isset($_SESSION['msg']) ? $_SESSION['msg'] : '');
        // unset session $msg
        unset($_SESSION['msg']);
        
        // get all file names in /data
        $files = scandir('data');
        // remove . and ..
        $files = array_slice($files, 2);

        // get all file date
        $dates = [];
        // boolean array $switch
        $switch = [];
        foreach ($files as $file) {
            // get date from file
            $dates[] = date('Y-m-d', filemtime('data/' . $file));
            // if date is today, switch is true, else false
            $switch[] = date('Y-m-d') == date('Y-m-d', filemtime('data/' . $file));
        }
        $f3->set('dates', $dates);
        $f3->set('files', $files);
        $f3->set('switch', $switch);
        echo Template::instance()->render('ui/index.html');

    });

    // route /@name/toggle
    $f3->route('GET /@name/toggle', function($f3, $params) {
        // get file name
        $file = 'data/' . $params['name'];
        // update file date
        touch($file);
        // redirect to /
        $f3->reroute('/');
    });

    // route /@name/delete
    $f3->route('GET /@name/delete', function($f3, $params) {
        // check if file exists
        if (!file_exists('data/' . $params['name'])) {
            // set session $msg
            $_SESSION['msg'] = 'Reminder does not exist';
            // redirect to /
            $f3->reroute('/');
        }
        // delete file
        unlink('data/' . $params['name']);
        // set session $msg
        $_SESSION['msg'] = 'Reminder deleted';
        // redirect to /
        $f3->reroute('/');
    });

    // route POST /
    $f3->route('POST /', function($f3) {
        // get new file name
        $file = 'data/' . $f3->get('POST.file');
        // validate file name, accept space
        if (!preg_match('/^[a-zA-Z0-9 ]+$/', $f3->get('POST.file'))) {
            // redirect to /
            $f3->reroute('/');
        }
        // check if file exists
        if (file_exists($file)) {
            // session $msg
            $_SESSION['msg'] = 'Reminder already exists';
            // redirect to /
            $f3->reroute('/');
        }
        // create file
        touch($file);
        // set session $msg
        $_SESSION['msg'] = 'Reminder created';
        // redirect to /
        $f3->reroute('/');
    });

    // route /@name/date/@date
    $f3->route('GET /@name/date/@date', function($f3, $params) {
        // get file name
        $file = 'data/' . $params['name'];
        // validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['date'])) {
            // redirect to /
            $f3->reroute('/');
        }
        // update file date
        touch($file, strtotime($params['date']));
        // redirect to /
        $f3->reroute('/');
    });

    // run f3
    $f3->run();
?>