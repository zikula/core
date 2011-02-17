<?php
$config = array();
$config['charset'] = 'utf-8';
$config['tempdir'] = 'ztemp';
$config['cachetype'] = 'array'; // 'array', 'disk', 'inputstream', 'null'
$config['sendmethod'] = 'normal'; // normal, single_recipient
$config['transport']['type'] = 'smtp';// 'smtp', 'sendmail', 'mail'

// configuration for smtp
$config['transport']['smtp']['host'] = 'localhost';
$config['transport']['smtp']['port'] = '25';
$config['transport']['smtp']['security'] = ''; // ssl, tls
$config['transport']['smtp']['username'] = '';
$config['transport']['smtp']['password'] = '';

// configuration for sendmail
$config['transport']['sendmail']['command'] = '-bs'; // -bs, -t

//configuration for mail which uses PHP's mail() function - not recommended
$config['transport']['mail']['extraparams'] = ''; // Uses PHP mail()

