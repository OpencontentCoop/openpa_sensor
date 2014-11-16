<?php
$Module = array( 'name' => 'Sensor' );

$ViewList = array();
$ViewList['home'] = array(
	'script' =>	'home.php',
	'functions' => array( 'use' )
);

$ViewList['info'] = array(
    'script' =>	'info.php',
    'params' => array( 'Page' ),
    'functions' => array( 'use' )
);

$ViewList['posts'] = array(
    'script' =>	'posts.php',
    'params' => array( 'ID', 'Offset' ),
    'functions' => array( 'use' )
);

$ViewList['signup'] = array(
    'script' =>	'signup.php',
    'params' => array(),
    'functions' => array( 'use' )
);

$ViewList['add'] = array(
    'script' =>	'add.php',
    'params' => array(),
    'functions' => array( 'use' )
);

$ViewList['redirect'] = array(
    'script' =>	'redirect.php',
    'params' => array( 'View' ),
    'functions' => array( 'use' )
);


$FunctionList = array();
$FunctionList['use'] = array();

