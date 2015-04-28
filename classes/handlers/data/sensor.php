<?php

class DataHandlerSensor implements OpenPADataHandlerInterface
{
    public $contentType = 'geojson';
    
    public function __construct( array $Params )
    {
        $this->contentType = eZHTTPTool::instance()->getVariable( 'contentType', $this->contentType );
    }
    
    public function getData()
    {
        $data = array();
        if ( $this->contentType == 'geojson' )
        {
            $data = ObjectHandlerServiceControlSensor::fetchSensorGeoJsonFeatureCollection();
        }
        elseif ( $this->contentType == 'marker' )
        {
            $postId = eZHTTPTool::instance()->getVariable( 'id', $this->contentType );
            $cacheFilePath = SensorModuleFunctions::sensorPostCacheFilePath( null, $postId, array(), 'popup' );
            $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );            
            $ini = eZINI::instance();
            $viewCacheEnabled = ( $ini->variable( 'ContentSettings', 'ViewCaching' ) == 'enabled' );    
            if ( $viewCacheEnabled )
            {
                $Result = $cacheFile->processCache( array( 'SensorModuleFunctions', 'sensorCacheRetrieve' ),
                                                    array( 'SensorModuleFunctions', 'sensorPostPopupGenerate' ),
                                                    null,
                                                    null,
                                                    $postId );
            }
            else
            {    
                $data = SensorModuleFunctions::sensorPostPopupGenerate( false, $postId );
                $Result = $data['content']; 
            }
            
            $data = array( 'content' => $Result );
        }
        return $data;        
    }
}