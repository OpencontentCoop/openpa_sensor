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
        $this->data['tipo'] = $type['identifier'] == 'suggerimento' ? 3 : $type['identifier'] == 'reclamo' ? 2 : 1;         
        
        $author = '?';
        $email = '?';
        if ( $this->object->attribute( 'owner' ) instanceof eZContentObject )
        {
            $author = $this->object->attribute( 'owner' )->attribute( 'name' );            
            $behalf = $this->post->objectHelper->getContentObjectAttribute( 'on_behalf_of' );
            if ( $behalf instanceof eZContentObjectAttribute && $behalf->hasContent() )
            {
                $author .= ' (' . $behalf->toString() . ')';     
            }
            $authorUser = eZUser::fetch( $this->object->attribute( 'owner' )->attribute( 'id' ) );
            if ( $authorUser instanceof eZUser )
            {
                $email = $authorUser->attribute( 'email' );
            }
        }
        $this->data['segnalatore'] = substr( $author, 0, 47 ) . '...';
        $this->data['email'] = $email;
        
        $description = '';        
        $subjectAttribute = $this->post->objectHelper->getContentObjectAttribute( 'subject' );
        if ( $subjectAttribute && $subjectAttribute->hasContent() )
            $description = '== ' . $subjectAttribute->toString() . ' == ';
        $descriptionAttribute = $this->post->objectHelper->getContentObjectAttribute( 'description' );
        if ( $descriptionAttribute )
            $description .= $descriptionAttribute->toString();
        $this->data['descrizione'] = $description;
        
        $address = '?';
        $latitude = '?';
        $longitude = '?';
        $geoAttribute = $this->post->objectHelper->getContentObjectAttribute( 'geo' );
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
        
        $this->data['spi'] = 0; //@todo
        $privacyState = $this->handler->attribute( 'current_privacy_state' );
        $this->data['visibilita'] = (int) $privacyState['identifier'] == 'public';
        
        if ( $this->outputDebug )
        {
            print_r( $this->data );
        }
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
    
    protected function makePending()
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
            OpenPABase::sudo(
                function () use ( $object, $state )
                {
                    if ( eZOperationHandler::operationIsAvailable( 'content_updateobjectstate' ) )
                    {
                        eZOperationHandler::execute( 'content', 'updateobjectstate',
                            array( 'object_id' => $object->attribute( 'id' ),
                                   'state_id_list' => array( $state->attribute( 'id' ) ) ) );
                    }
                    else
                    {
                        eZContentOperationCollection::updateObjectState( $object->attribute( 'id' ), array( $state->attribute( 'id' ) ) );
                    }
                }
            );
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
        
            eZPendingActions::removeByAction(
                TrentoWsSensorPost::PENDING_ACTION_SEND_TO_WS,
                array(
                    'param' => array( $postIDList )
                )
            );
        }
    }
    
}