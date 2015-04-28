<?php
/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$postId = $Params['ID'];
$Offset = $Params['Offset'];
if ( !is_numeric( $Offset ) )
    $Offset = 0;


if ( !is_numeric( $postId ) )
{
    $node = ObjectHandlerServiceControlSensor::postContainerNode();
    $module->redirectTo( $node->attribute( 'url_alias' ) );
}
else
{
    eZPreferences::sessionCleanup();
    
    $viewParameters = array( 'offset' => $Offset );
    $user = eZUser::currentUser();
    $cacheFilePath = SensorModuleFunctions::sensorPostCacheFilePath( $user, $postId, $viewParameters );    
    $localVars = array( "cacheFilePath", "postId", "module", "tpl", 'viewParameters' );        
    $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
    $args = compact( $localVars );
    $ini = eZINI::instance();
    $viewCacheEnabled = ( $ini->variable( 'ContentSettings', 'ViewCaching' ) == 'enabled' );    
    if ( $viewCacheEnabled )
    {
        $Result = $cacheFile->processCache( array( 'SensorModuleFunctions', 'sensorCacheRetrieve' ),
                                            array( 'SensorModuleFunctions', 'sensorPostGenerate' ),
                                            null,
                                            null,
                                            $args );
    }
    else
    {    
        $data = SensorModuleFunctions::sensorPostGenerate( false, $args );
        $Result = $data['content']; 
    }
    return $Result;
}
