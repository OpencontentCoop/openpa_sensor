<?php

/**
 *
 * Per controllare i pending
 * php extension/openpa_sensor/classes/custom/trento/script/ws.php -strentosensor_backend -v --list_pendings
 *
 * Per inviare i pending
 * php extension/openpa_sensor/classes/custom/trento/script/ws.php -strentosensor_backend -v --send_pendings
 *
 * Per inviare
 * php extension/openpa_sensor/classes/custom/trento/script/ws.php -strentosensor_backend --id=1902 -v
 *
 */

class TrentoWsSensorPost
{
    const PENDING_ACTION_SEND_TO_WS = 'send_to_trento_ws_sensor';
    
    protected $data = array(
        'id' => null,
        'operazione' => null,
        'tipo' => null,
        'modalita_comunicazione' => 8,
        'categoria' => 1, //default la prima in attesa di un "Senza categoria"
        'segnalatore' => null,
        'email' => null,
        'descrizione' => null,  
        'indirizzo_codice' => '',
        'indirizzo_via' => null,
        'indirizzo_numero' => '',
        'indirizzo_barra' => '',
        "latitudine" => null,
        "longitudine" => null,
        'url' => null,
        'spi' => null,
        'visibilita' => null
    );

    /**
     * @var SensorPost
     */
    protected $post;

    /**
     * @var eZContentObject
     */
    protected $object;
    
    protected $handler;
    
    public static $stateGroupIdentifier = 'sensor_ws';
    public static $stateIdentifiers = array(
        'new' => "Non ancora inviato",
        'sent' => "Inviato"
    );
    
    protected $states;
    
    public $outputDebug = false;
    
    public static $logFileName = 'trento_ws_sensorcivico.log';
    
    public function __construct( SensorPost $post, $outputDebug = false )
    {
        $this->outputDebug = $outputDebug;
        
        $this->states = OpenPABase::initStateGroup( self::$stateGroupIdentifier, self::$stateIdentifiers );
        
        $this->post = $post;
        $this->object = $this->post->objectHelper->getContentObject();
        $this->handler = SensorHelper::instanceFromContentObjectId( $this->object->attribute( 'id' ) );
        
        $this->data['id'] = $this->handler->attribute( 'id' );
        
        $this->data['operazione'] = $this->isNew() ? 'ADD' : 'UPDATE';
        
        $type = $this->handler->attribute( 'type' );
        
        $this->data['tipo'] = 1;
        if ( $type['identifier'] == 'suggerimento' )
        {
            $this->data['tipo'] = 3;    
        }
        elseif ( $type['identifier'] == 'reclamo' )
        {
            $this->data['tipo'] = 2;    
        }
        elseif ( $type['identifier'] == 'segnalazione' )
        {
            $this->data['tipo'] = 1;    
        }        
        
        $author = '?';
        $email = '?';
        if ( $this->object->attribute( 'owner' ) instanceof eZContentObject )
        {
            $author = $this->object->attribute( 'owner' )->attribute( 'name' );            
            $behalf = $this->post->objectHelper->getContentObjectAttribute( 'on_behalf_of' );
            if ( $behalf instanceof eZContentObjectAttribute && $behalf->hasContent() )
            {
                $author = $behalf->toString();
                $behalfDetails = $this->post->objectHelper->getContentObjectAttribute( 'on_behalf_of_detail' );
                if ( $behalfDetails instanceof eZContentObjectAttribute && $behalfDetails->hasContent() )
                {
                    $author .=  ', ' . $behalfDetails->toString();     
                }
            }
            $authorUser = eZUser::fetch( $this->object->attribute( 'owner' )->attribute( 'id' ) );
            if ( $authorUser instanceof eZUser )
            {
                $email = $authorUser->attribute( 'email' );
            }
        }
        $this->data['segnalatore'] = mb_strlen( $author ) > 197 ? substr( $author, 0, 197 ) . '...' : $author;
        $this->data['email'] = $email;
        
        $description = '';        
        $subjectAttribute = $this->post->objectHelper->getContentObjectAttribute( 'subject' );
        if ( $subjectAttribute && $subjectAttribute->hasContent() )
            $description = ' ' . $subjectAttribute->toString() . '

 ';
        $descriptionAttribute = $this->post->objectHelper->getContentObjectAttribute( 'description' );
        if ( $descriptionAttribute )
            $description .= $descriptionAttribute->toString();
        $this->data['descrizione'] = $description;
        
        $address = '?';
        $latitude = '?';
        $longitude = '?';
        $geoAttribute = $this->post->objectHelper->getContentObjectAttribute( 'geo' );

        if ( !$geoAttribute instanceof eZContentObjectAttribute || ( $geoAttribute instanceof eZContentObjectAttribute && !$geoAttribute->hasContent() ) )
        {            
            $areas = ObjectHandlerServiceControlSensor::areas();
            $firstArea = $areas['tree'][0]['node'];
            if ( $firstArea instanceof eZContentObjectTreeNode )
            {
                $firstAreaDataMap = $firstArea->attribute( 'data_map' );
                if ( isset( $firstAreaDataMap['geo'] ) && $firstAreaDataMap['geo']->hasContent() )
                {
                    $geoAttribute = $firstAreaDataMap['geo'];
                }
            }
        }
        
        if ( $geoAttribute )
        {
            $geoAttributeContent = $geoAttribute->attribute( 'content' );            
            $address = $geoAttributeContent->attribute( 'address' );
            $latitude = $geoAttributeContent->attribute( 'latitude' );
            $longitude = $geoAttributeContent->attribute( 'longitude' );
        }        
        $this->data['indirizzo_via'] = $address;
        $this->data['latitudine'] = $latitude;
        $this->data['longitudine'] = $longitude;
        
        $this->data['url'] = $this->handler->attribute( 'post_url' );
                
        $this->data['spi'] = 0;
        $spi = $this->post->objectHelper->getContentObjectAttribute( 'spi' );
        if ( $spi instanceof eZContentObjectAttribute && $spi->attribute( 'data_int' ) == 1 )
        {
            $this->data['spi'] = 1;    
        }
        
        $behalfMode = $this->post->objectHelper->getContentObjectAttribute( 'on_behalf_of_mode' );
        if ( $behalfMode instanceof eZContentObjectAttribute && $behalfMode->hasContent() )
        {
            $this->data['modalita_comunicazione'] = $this->parseMode( $behalfMode );    
        }
        
        $privacyState = $this->handler->attribute( 'current_privacy_state' );        
        $this->data['visibilita'] = intval( $privacyState['identifier'] == 'public' );
        
        if ( $this->outputDebug )
        {
            print_r( $this->data );
        }
    }
    
    protected function parseMode( eZContentObjectAttribute $behalfMode )
    {
        $value = 8;
        $stringValue = $behalfMode->toString();
        $data = array(
            '1' => 'Telefono',
            '2' => 'Posta o Fax',
            '3' => 'E-mail',
            '4' => 'Rete Civica In dirett@',
            '5' => 'Front office',
            '6' => 'Bussola',
            '8' => 'sensoRcivico',
            '9' => 'Linea verde',
            '10' => 'Sul posto',
            '11' => 'Richiesta Difensore civico',
            '12' => 'Istanza / Petizione',
            '13' => 'Aiutaci a migliorare',
            '14' => 'WhatsApp',
        );
        foreach( $data as $id => $match )
        {
            if ( strtolower( $stringValue ) == strtolower( $match ) )
            {
                $value = $id;
                break;
            }
        }
        return $value;
    }
    
    protected function isNew()
    {
        $state = $this->states['sensor_ws.new'];
        if ( $state instanceof eZContentObjectState )
        {
            return in_array( $state->attribute( 'id' ), $this->object->attribute( 'state_id_array' ) );
        }
        throw new Exception( "State 'sensor_ws.new' not found" );
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function send()
    {
        $message = "(internal error)";
        $result = false;
        
        $settings = eZINI::instance( 'ocsensor.ini' )->group( 'TrentoWebServiceSettings' );
        $client = new ggPhpSOAPClient( $settings['Server'], $settings['Path'], $settings['Port'], $settings['Protocol'], $settings['Wsld'] );
        $request = new ggPhpSOAPRequest( "setComunicazione", array( $this->data ) );
        /** @var ggPhpSOAPResponse $response */
        $response = $client->send( $request );
        if ( $this->outputDebug )
        {
            print_r( $response );
        }
        if ( $response->isFault() )
        {
            $message = "Fault: " . $response->faultCode(). " - \"" . $response->faultString() . "\"";
            $result = false;            
        }
        else
        {
            $returnData = $response->value();            
            if ( isset( $returnData['setComunicazioneReturn'] ) )
            {
                if ( $returnData['setComunicazioneReturn']['messaggio'] )
                    $message = $returnData['setComunicazioneReturn']['messaggio'];
                $result = $returnData['setComunicazioneReturn']['risposta'];                
            }
            else
            {
                $message = "(internal error) setComunicazioneReturn non trovato";
                $result = false;
            }
        }        
        $this->log( $message, $result );
        if ( $result == true )
        {
            if ( $this->isNew() )
                $this->setSent();            
        }
        else
        {
            $this->makePending();            
        }
        return $result;
    }

    public function makePending()
    {
        $pendingAction = eZPendingActions::fetchObject( eZPendingActions::definition(), null, array( 'action' => TrentoWsSensorPost::PENDING_ACTION_SEND_TO_WS, 'param' => $this->object->attribute( 'id' ) ) );        
        if ( !$pendingAction instanceof eZPendingActions )
        {
            $pendingAction = new eZPendingActions(
                array(
                    'action' => TrentoWsSensorPost::PENDING_ACTION_SEND_TO_WS,
                    'created' => time(),
                    'param' => $this->object->attribute( 'id' )
                )
            );
            $pendingAction->store();
        }
        if ( $this->outputDebug )
        {
            print_r( $pendingAction );
        }
    }
    
    protected function setSent()
    {
        $state = $this->states['sensor_ws.sent'];  
        if ( $state instanceof eZContentObjectState )
        {
            $object = $this->object;

            $object->assignState( $state );
            eZSearch::updateObjectState($object->attribute( 'id' ), array( $state->attribute( 'id' )));
            eZContentCacheManager::clearContentCache( $object->attribute( 'id' ) );
        }
        else
        {
            throw new Exception( "State 'sensor_ws.sent' not found" );
        }
    }
    
    public function log( $message, $result )
    {
        $logData = array();
        $logData['id'] = $this->object->attribute( 'id' );
        $logData['result'] = intval( $result );
        $logData['message'] = $message;
        $logData['data'] = var_export( $this->data, 1 );
        
        if ( $this->outputDebug )
        {
            print_r( $logData );
        }
        
        $log = implode( " - ",  $logData );
        eZLog::write( $log, self::$logFileName );        
    }
    
    public static function listPendingItems()
    {
        $entries = eZPendingActions::fetchByAction( TrentoWsSensorPost::PENDING_ACTION_SEND_TO_WS );
        return $entries;
    }
    
    public static function sendPendingItems( $outputDebug = false )
    {        
        $entries = self::listPendingItems();
        
        if ( !empty( $entries ) )
        {
            $postIDList = array();
            foreach ( $entries as $entry )
            {
                $postID = $entry->attribute( 'param' );                
        
                try
                {
                    $helper = SensorHelper::instanceFromContentObjectId( $postID );
                    if ( $helper instanceof SensorHelper )
                    {
                        $post = $helper->currentSensorPost;
                        $wsPost = new TrentoWsSensorPost( $post, $outputDebug );
                        if ( $wsPost->send() )
                        {
                            $postIDList[] = (int)$postID;
                        }
                    }
                }
                catch( Exception $e )
                {
                    eZLog::write( $e->getMessage(), self::$logFileName );  
                }
            }
        
            if (!empty($postIDList)) {
                eZPendingActions::removeByAction(
                    TrentoWsSensorPost::PENDING_ACTION_SEND_TO_WS,
                    array(
                        'param' => array( $postIDList )
                    )
                );
            }
        }
    }
    
}
