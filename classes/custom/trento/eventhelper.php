<?php

class TrentoSensorPostEventHelper extends SensorPostEventHelper
{
    public function createEvent( $eventName, $eventDetails = array() )
    {        
        parent::createEvent( $eventName, $eventDetails );
        
        if ( $eventName == 'on_create' || $eventName == 'on_update' )
        {
            try
            {
                $wsPost = new TrentoWsSensorPost( $this->post );
                $wsPost->send();
            }
            catch( Exception $e )
            {
                eZLog::write( $this->post->objectHelper->getContentObject()->attribute( 'id' ) . ' - ' . $e->getMessage(), TrentoWsSensorPost::$logFileName );
            }
        }
    }
}