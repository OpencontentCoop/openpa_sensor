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
            $id = eZHTTPTool::instance()->getVariable( 'id', $this->contentType );
            $object = eZContentObject::fetch( $id );
            if ( $object instanceof eZContentObject && $object->attribute( 'can_read' ) )
            {
                $tpl = eZTemplate::factory();
                $tpl->setVariable( 'object', $object );
                $result = $tpl->fetch( 'design:sensor/marker_popup.tpl' );
                $data = array( 'content' => $result );
            }
            else
            {
                $data = array( 'content' => '<em>Private</em>' );
            }
        }
        return $data;        
    }
}
