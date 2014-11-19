<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();

$offset = !is_numeric( $Params['Offset'] ) ? 0 : $Params['Offset'];
$sortBy = 'modified';
$sortOrder = false;
$isRead = null;
$isActive = null;
$status = false;

$itemCount = 0;
$itemList = array();
$itemLimit = 20;
$currentUser = eZUser::currentUser();

if ( !$currentUser->isAnonymous() )
{
    $group = SensorHelper::currentUserCollaborationGroup();
    if ( $group instanceof eZCollaborationGroup )
    {
        $itemCount = eZFunctionHandler::execute(
            'collaboration',
            'item_count',
            array( 'parent_group_id' => $group->attribute( 'id' ) )
        );
        if ( $itemCount > 0 )
        {            
            $itemParameters = array(
                'offset' => $offset,
                'limit' => $itemLimit,
                'sort_by' => array( $sortBy, $sortOrder ),
                'is_read' => $isRead,
                'is_active' => $isActive,
                'parent_group_id' => $group->attribute( 'id' ),
                'status' => $status
            );        
            $itemList = SensorHelper::fetchListTool( $itemParameters, false );            
        }
    }
}

$tpl->setVariable( 'item_count', $itemCount );
$tpl->setVariable( 'item_list', $itemList );
$tpl->setVariable( 'item_limit', $itemLimit );

$Result = array();

$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
$Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
$Result['content'] = $tpl->fetch( 'design:sensor/dashboard.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'sensor/home' );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();