<?php

class TrentoWsSensorPost
{
    protected $data = array(
        'id' => null,
        'operazione' => null,
        'tipo' => null,
        'modalita_comunicazione' => 8,
        'categoria' => 0,
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
    
    protected $post;
    
    protected $object;
    
    protected $handler;
    
    public static $stateGroupIdentifier = 'sensor_ws';
    public static $stateIdentifiers = array(
        'new' => "Non ancora inviato",
        'sent' => "Inviato"
    );
    
    protected $states;
    
    public $outputDebug = false;
    
    public $logFileName = 'trento_ws_sensorcivico.log';
    
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
        $this->data['segnalatore'] = $author;
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
        return $result;
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
        eZLog::write( $log, $this->logFileName );
        
        $state = $this->states['sensor_ws.sent'];        
        if ( $state instanceof eZContentObjectState )
        {
            if ( $result == true && $this->isNew() )
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
        }
        else
        {
            throw new Exception( "State 'sensor_ws.sent' not found" );
        }
    }
    
}