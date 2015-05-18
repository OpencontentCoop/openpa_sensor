<?php

//
//SensorHelper::deleteCollaborationStuff( 16 );
//SensorHelper::deleteCollaborationStuff( 17 );
//
//$db = eZDB::instance();
//$db->begin();
//$res = $db->arrayQuery( "SELECT id FROM ezcollab_item WHERE data_int1 = 1841" );
//$db->commit();
//echo '<pre>';print_r($res);die();


$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$part = !is_string( $Params['Part'] ) ? false : $Params['Part'];
$offset = !is_numeric( $Params['Offset'] ) ? 0 : $Params['Offset'];
$groupId = !is_numeric( $Params['Group'] ) ? false : $Params['Group'];
$export = !is_string( $Params['Export'] ) ? false : strtolower( $Params['Export'] ); 

$currentPart = false;
$availableParts = array();
if ( ObjectHandlerServiceControlSensor::PostIsEnable() )
{
    $availableParts['Segnalazioni'] = 'post';
}
if ( ObjectHandlerServiceControlSensor::ForumIsEnable() )
{
    $availableParts['Dimmi'] = 'forum';
}
if ( ObjectHandlerServiceControlSensor::SurveyIsEnabled() )
{
    $availableParts['Consultazioni'] = 'survey';
}
if ( !$part && count( $availableParts ) > 0 )
{
    $part = current( $availableParts );
}

if ( in_array( $part, $availableParts ) )
{
    $currentPart = $part;
}

$tpl->setVariable( 'current_dashboard', $currentPart );
$tpl->setVariable( 'available_dashboard', $availableParts );

$selectedList = $Params['List'];

$limit = 15;

$currentUser = eZUser::currentUser();

$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'limit', $limit );
$viewParameters = array( 'offset' => $offset );
$tpl->setVariable( 'view_parameters', $viewParameters );

$access = $currentUser->hasAccessTo( 'sensor', 'manage' );
$tpl->setVariable( 'simplified_dashboard', $access['accessWord'] == 'no' );

if ( $currentUser->isAnonymous() )
{
    $module->redirectTo( 'sensor/home' );
    return;
}
else
{
    if ( $part == 'post' && ObjectHandlerServiceControlSensor::PostIsEnable() )
    {
        if ( $groupId )
        {
            $group = eZPersistentObject::fetchObject(
                eZCollaborationGroup::definition(),
                null,
                array( 'user_id' => eZUser::currentUserID(), 'id' => $groupId )
            );
        }
        else
        {
            $group = SensorHelper::currentUserCollaborationGroup();
        }

        if ( $group instanceof eZCollaborationGroup )
        {
            $access = $currentUser->hasAccessTo( 'sensor', 'manage' );
            if ( $access['accessWord'] == 'no' )
            {
                $items = SensorHelper::fetchAllItems( array(), $group, $limit, $offset );
                $itemsCount = SensorHelper::fetchAllItemsCount( array(), $group );
                $tpl->setVariable( 'simplified_dashboard', true );
                $tpl->setVariable( 'all_items', $items );
                $tpl->setVariable( 'all_items_count', $itemsCount );
            }
            else
            {
                $listTypes = SensorHelper::availableListTypes();
                $filters = $http->hasGetVariable( 'filters' ) ? $http->getVariable( 'filters' ) : array();                
                $availableFilters = array( 'id', 'subject', 'category', 'creator_id', 'creation_range', 'owner' );                
                foreach( $filters as $key => $filter )
                {
                    if ( !in_array( $key, $availableFilters ) || empty( $filter ) )
                    {
                        unset( $filters[$key] );
                    }                    
                }
                
                if ( $export )
                {
                    try
                    {
                        $exporter = SensorHelper::instantiateExporter( $export, $filters, $group, $selectedList );
                        ob_get_clean(); //chiudo l'ob_start dell'index.php
                        $exporter->handleDownload();                        
                        eZExecution::cleanExit();
                    }
                    catch( Exception $e )
                    {
                        $module->redirectTo( 'sensor/home' );
                        return;
                    }
                }
                else
                {
                    
                    $filtersQuery = count( $filters ) > 0 ? '?' . http_build_query( array( 'filters' => $filters ) ) : '';
                    $currentList = false;
                    foreach( $listTypes as $key => $type )
                    {
                        $count = call_user_func( $type['count_function'], $filters, $group );
                        $listTypes[$key]['count'] = call_user_func( $type['count_function'], $filters, $group );
                        if ( $selectedList == $type['identifier'] || ( !$selectedList && $count > 0 && $currentList == false ) )
                        {
                            $currentList = $listTypes[$key];
                        }
                    }
    
                    if ( $currentList == false )
                    {
                        $currentList = $listTypes[0];
                    }
                    
                    $items = call_user_func( $currentList['list_function'], $filters, $group, $limit, $offset );
                    $tpl->setVariable( 'items', $items );
                }

                $tpl->setVariable( 'filters', $filters );
                $tpl->setVariable( 'filters_query', $filtersQuery );
                $tpl->setVariable( 'simplified_dashboard', false );
                $tpl->setVariable( 'current_list', $currentList );
                $tpl->setVariable( 'list_types', $listTypes );

            }
        }
        else
        {
            $module->redirectTo( 'sensor/home' );
            return;
        }
    }
    
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
}