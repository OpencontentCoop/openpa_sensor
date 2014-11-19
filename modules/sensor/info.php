<?php

$Module = $Params['Module'];
$tpl = eZTemplate::factory();
$identifier = $Params['Page'];

$currentUser = eZUser::currentUser();

$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'persistent_variable', array() );
$tpl->setVariable( 'identifier', $identifier );

$Result = array();

$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
$Result['content'] = $tpl->fetch( 'design:sensor/info.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'sensor/info' );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();