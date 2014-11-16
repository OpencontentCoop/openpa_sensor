<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();

$currentUser = eZUser::currentUser();

$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'persistent_variable', array() );
$tpl->setVariable( 'sensor_home', true );

$Result = array();

$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
$Result['content'] = $tpl->fetch( 'design:sensor/home.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'sensor/home' );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();