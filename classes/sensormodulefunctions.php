<?php

class SensorModuleFunctions
{
    const GLOBAL_PREFIX = 'global-';
    
    public static function onClearObjectCache( $nodeList )
    {  
        $rootNode = ObjectHandlerServiceControlSensor::rootNode();
        if ( $rootNode instanceof eZContentObjectTreeNode
             && in_array( $rootNode->attribute( 'node_id' ), $nodeList ) )
        {
            self::clearSensorCache( self::GLOBAL_PREFIX );
        }
        $postClass = ObjectHandlerServiceControlSensor::postContentClass();
        $objects = eZContentObject::fetchByNodeID( $nodeList );
        foreach( $objects as $object )
        {
            if ( $object->attribute( 'contentclass_id' ) == $postClass->attribute( 'id' ) )
            {
                $postId = $object->attribute( 'id' );
                $extraPath = "post/" . eZDir::filenamePath( $postId );
                self::clearSensorCache( "$extraPath$postId-" );
            }
        }
        return $nodeList;
    }
    
    protected static function clearSensorCache( $prefix )
    {
        $siteAccesses = array();
        $ini = eZINI::instance();
        if ( $ini->hasVariable( 'SiteAccessSettings', 'RelatedSiteAccessList' ) &&
             $relatedSiteAccessList = $ini->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' ) )
        {
            if ( !is_array( $relatedSiteAccessList ) )
            {
                $relatedSiteAccessList = array( $relatedSiteAccessList );
            }
            $relatedSiteAccessList[] = $GLOBALS['eZCurrentAccess']['name'];
            $siteAccesses = array_unique( $relatedSiteAccessList );
        }
        else
        {
            $siteAccesses = $ini->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );
        }            
        if ( !empty( $siteAccesses ) )
        {                
            $cacheBaseDir = eZDir::path( array( eZSys::cacheDirectory(), 'sensor' ) );                
            $fileHandler = eZClusterFileHandler::instance();
            $fileHandler->fileDeleteByDirList( $siteAccesses, $cacheBaseDir, $prefix );
        }
    }
    
    public static function sensorHomeGenerate( $file, $args )
    {
        $currentUser = eZUser::currentUser();
        
        $tpl = eZTemplate::factory();        
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
        $retval = array( 'content' => $Result,
                         'scope'   => 'sensor' );
        return $retval;
    }
    
    public static function sensorInfoGenerate( $file, $args )
    {
        extract( $args );
        
        $tpl = eZTemplate::factory();
        $identifier = $Params['Page'];        
        if ( ObjectHandlerServiceControlSensor::rootHandler()->hasAttribute( $identifier ) )
        {
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
            
            $retval = array( 'content' => $Result,
                             'scope'   => 'sensor' );
        }
        else
        {
            return  array( 'content' => $Params['Module']->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' ),
                           'store'   => false );
        }
        return $retval;
    }    
    
    public static function sensorPostPopupGenerate( $file, $args )
    {        
        $object = eZContentObject::fetch( $args );
        if ( $object instanceof eZContentObject && $object->attribute( 'can_read' ) )
        {
            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'object', $object );
            $Result = $tpl->fetch( 'design:sensor/parts/post/marker_popup.tpl' );
            $data = array( 'content' => $result );
        }
        else
        {
            $Result = '<em>Private</em>';
        }
        $retval = array( 'content' => $Result,
                         'scope'   => 'sensor' );
        return $retval;
    }
    
    public static function sensorPostGenerate( $file, $args )
    {
        extract( $args );
        
        try
        {
            $object = eZContentObject::fetch( $postId );            
            if ( !$object instanceof eZContentObject )
            {                
                return  array( 'content' => $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' ),
                               'store'   => false );
            }
    
            if ( !$object->attribute( 'can_read' ) )
            {                
                return  array( 'content' => $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' ),
                               'store'   => false );
            }
    
            /** @var ObjectHandlerServiceControlSensor $sensor */
            $post = OpenPAObjectHandler::instanceFromContentObject( $object )->attribute( 'control_sensor' );
    
            /** @var SensorHelper $helper */
            $helper = $post->attribute( 'helper' );
    
            /** @var eZCollaborationItem $collaborationItem */
            $collaborationItem = $helper->attribute( 'collaboration_item' );
    
            /** @var OpenPASensorCollaborationHandler $collaborationHandler */
            $collaborationHandler = $collaborationItem->handler();
    
            $collaborationItem->handleView( 'full' );
    
            $tpl->setVariable( 'view_parameters', $viewParameters );
            $tpl->setVariable( 'post', $post );
            $tpl->setVariable( 'helper', $helper );
            $tpl->setVariable( 'object', $object );
            $tpl->setVariable( 'collaboration_item', $collaborationItem );
    
            $currentParticipant = eZFunctionHandler::execute(
                "collaboration",
                "participant",
                array(  "item_id" => $collaborationItem->attribute( 'id' ) )
            );
    
            $participantList = $helper->fetchParticipantMap();
    
            $tpl->setVariable( 'current_participant', $currentParticipant );
            $tpl->setVariable( 'participant_list', $participantList );
    
            $tpl->setVariable( 'sensor_post', true );
            $tpl->setVariable( 'post_geo_array_js', $post->attribute( 'geo_js_array' ) );
    
            $collaborationHandler->readItem( $collaborationItem );
            $helper->storePostActivesParticipants();
        }
        catch( Exception $e )
        {
            $tpl->setVariable( 'error', $e->getMessage() );
        }
    
        $Result = array();
        $Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        $Result['pagelayout'] = 'design:sensor/pagelayout.tpl';
        $Result['content'] = $tpl->fetch( 'design:sensor/parts/post/full.tpl' );
        $Result['node_id'] = 0;
    
        $contentInfoArray = array( 'url_alias' => 'sensor/post/' . $postId );
        $contentInfoArray['persistent_variable'] = false;
        if ( $tpl->variable( 'persistent_variable' ) !== false )
        {
            $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
        }
        $Result['content_info'] = $contentInfoArray;
        $Result['path'] = array();
        
        return array( 'content' => $Result,
                      'scope'   => 'sensor' );
    }

    public static function sensorCacheRetrieve( $file, $mtime, $args )
    {
        $Result = include( $file );        
        return $Result;
    }
    
    public static function sensorPostCacheFilePath( $user, $postId, $viewParameters, $cacheNameExtra = '' )
    {
        $cacheHashArray = array();
        
        if ( $user instanceof eZUser )
        {            
            $cacheHashArray[] = implode( '.', $user->roleIDList() );
            
            $limitValueList = implode( '.', $user->limitValueList() );
            if ( !empty( $limitValueList ) )
            {
                $cacheHashArray[] = $limitValueList;
            }
            if ( !$user->isAnonymous()  )
            {
                $activeParticipants = SensorHelper::getStoredActivesParticipantsByPostId( $postId );
                if ( in_array( $user->id(), $activeParticipants ) )
                {
                    $cacheHashArray[] = 'ap:' .$user->id();
                }
            }
        }
        
        //@todo? al momento non serve mettere in cache i viewparameters perché non vengono usati
        //$vpString = '';
        //ksort( $viewParameters );
        //foreach ( $viewParameters as $key => $value )
        //{
        //    if ( !$key )
        //        continue;
        //    $vpString .= 'vp:' . $key . '=' . $value;
        //}
        //$cacheHashArray[] = $vpString;
        
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $cacheFile = $postId . '-' . $cacheNameExtra . md5( implode( '-', $cacheHashArray ) ) . '.cache';
        $extraPath = eZDir::filenamePath( $postId );
        $cachePath = eZDir::path( array( eZSys::cacheDirectory(), 'sensor', $currentSiteAccess, 'post', $extraPath, $cacheFile ) );
        
        return $cachePath;
    }

    public static function sensorGlobalCacheFilePath( $fileName )
    {
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $cacheFile = self::GLOBAL_PREFIX . $fileName . '.php';
        $cachePath = eZDir::path( array( eZSys::cacheDirectory(), 'sensor', $currentSiteAccess, $cacheFile ) );        
        return $cachePath;
    }
}