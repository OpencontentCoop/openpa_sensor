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

$ViewList['activate'] = array(
    'script' =>	'activate.php',
    'ui_context' => 'authentication',
    'params' => array( 'Hash', 'MainNodeID' ),
    'functions' => array( 'use' )
);

$ViewList['add'] = array(
    'script' =>	'add.php',
    'params' => array(),
    'functions' => array( 'use' )
);

$ViewList['edit'] = array(
    'script' =>	'edit.php',
    'params' => array( 'ID' ),
    'functions' => array( 'use' )
);

$ViewList['comment'] = array(
    'script' =>	'comment.php',
    'params' => array( 'ForumID', 'ForumReplyID' ),
    'functions' => array( 'use' )
);

$ViewList['dashboard'] = array(
    'script' =>	'dashboard.php',
    'params' => array( "Part", "Group" ),
    'unordered_params' => array(
        "list" => "List",
        "offset" => "Offset" ),
    'functions' => array( 'use' )
);

$ViewList['redirect'] = array(
    'script' =>	'redirect.php',
    'params' => array( 'View' ),
    'functions' => array( 'use' )
);

$ViewList['test_mail'] = array(
    'script' =>	'test_mail.php',
    'params' => array(),
    'functions' => array( 'debug' )
);

$ViewList['config'] = array(
    'script' =>	'config.php',
    'params' => array( "Part" ),
    'unordered_params' => array( 'offset' => 'Offset' ),
    'functions' => array( 'config' )
);

$ViewList['user'] = array(
    'script' =>	'user.php',
    'params' => array( "ID" ),
    'unordered_params' => array(),
    'functions' => array( 'config' )
);

$ViewList['dimmi'] = array(
    'script' =>	'dimmi.php',
    'params' => array(),
    'functions' => array( 'use' )
);

$ViewList['survey'] = array(
    'script' =>	'survey.php',
    'params' => array( 'NodeID' ),
    'functions' => array( 'use' )
);

$ViewList['survey_user_result'] = array(
    'script' =>	'survey_user_result.php',
    'params' => array( 'ContentObjectID', 'SurveyResultID' ),
    'functions' => array( 'use' )
);

$ViewList['alert'] = array(
    'script' =>	'alert.php',
    'params' => array(),
    'functions' => array( 'use' )
);

$ViewList['moderate'] = array(
    'script' =>	'moderate.php',
    'params' => array( 'ObjectID' ),
    'functions' => array( 'use' )
);

$FunctionList = array();
$FunctionList['use'] = array();
$FunctionList['debug'] = array();
$FunctionList['config'] = array();
$FunctionList['manage'] = array();
$FunctionList['behalf'] = array();

