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
        /** @var eZContentObject[] $objects */
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
        $returnValue = array( 'content' => $Result,
                         'scope'   => 'sensor' );
        return $returnValue;
    }
    
    public static function sensorInfoGenerate( $file, $args )
    {
        extract( $args );
        if ( isset( $Params ) && $Params['Module'] instanceof eZModule )
        {
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
                    $contentInfoArray['persistent_variable'] = $tpl->variable(
                        'persistent_variable'
                    );
                }
                $Result['content_info'] = $contentInfoArray;
                $Result['path'] = array();

                $returnValue = array(
                    'content' => $Result,
                    'scope' => 'sensor'
                );
            }
            else
            {
                /** @var eZModule $module */
                $module = $Params['Module'];
                $returnValue = array(
                    'content' => $module->handleError(
                        eZError::KERNEL_NOT_AVAILABLE,
                        'kernel'
                    ),
                    'store' => false
                );
            }
        }
        else
        {
            $returnValue = array(
                'content' => 'error',
                'store' => false
            );
        }
        return $returnValue;
    }    
    
    public static function sensorPostPopupGenerate( $file, $args )
    {        
        $object = eZContentObject::fetch( $args );
        if ( $object instanceof eZContentObject && $object->attribute( 'can_read' ) )
        {
            $helper = SensorHelper::instanceFromContentObjectId( $object->attribute( 'id' ) );
            $tpl = eZTemplate::factory();
            $tpl->setVariable( 'sensor_post', $helper );
            $Result = $tpl->fetch( 'design:sensor/parts/post/marker_popup.tpl' );
        }
        else
        {
            $Result = '<em>Private</em>';
        }
        $returnValue = array( 'content' => $Result,
                              'scope' => 'sensor' );
        return $returnValue;
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
    
            /** @var SensorHelper $helper */
            $helper = SensorHelper::instanceFromContentObjectId( $postId );
            $helper->onRead();

            $tpl->setVariable( 'view_parameters', isset( $viewParameters ) ? $viewParameters : array() );
            $tpl->setVariable( 'sensor_post', $helper );
            $tpl->setVariable( 'current_user', eZUser::currentUser() );

            $helper->currentSensorPost->storeActivesParticipants();
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
                $activeParticipants = SensorPost::getStoredActivesParticipantsByPostId( $postId );
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