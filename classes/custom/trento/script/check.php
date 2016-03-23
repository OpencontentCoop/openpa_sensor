<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Check di Sensor Webservice Trento\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[id:][action:][csv:]',
    '',
    array(
        'id' => 'Post id',
        'csv' => 'Percorso file csv',
        'action' => 'check (default) |close|open',
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );

/** @var eZUser $user */
$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

function executeAction( SensorHelper $helper, $action = null )
{

}

try
{
    $id = $options['id'];
    if ( is_numeric( $id ) )
    {
        $helper = SensorHelper::instanceFromContentObjectId( $id );
        executeAction( $helper, $options['action'] );

    }
    elseif ( $options['csv'] )
    {
        $csvOptions = new SQLICSVOptions( array(
            'csv_path'	=> $options['csv']
        ) );
        $doc = new SQLICSVDoc( $csvOptions );
        $doc->parse();

        $data = array();
        $states = array();
        foreach( $doc->rows as $row )
        {            
            $states[$row->chstat] = $row->chstat;
            try
            {
                $helper = SensorHelper::instanceFromContentObjectId( $row->chidsr );
                $statusId = $helper->currentSensorPost->getCurrentStatus();
                if ( $statusId == SensorPost::STATUS_WAITING ) $status = 'waiting';
                elseif ( $statusId == SensorPost::STATUS_READ ) $status = 'read';
                elseif ( $statusId == SensorPost::STATUS_ASSIGNED ) $status = 'assigned';
                elseif ( $statusId == SensorPost::STATUS_CLOSED ) $status = 'closed';
                elseif ( $statusId == SensorPost::STATUS_FIXED ) $status = 'fixed';
                elseif ( $statusId == SensorPost::STATUS_REOPENED ) $status = 'reopened';
                else $status = '?';                
                
/*
K = Comunicata
A = Aperta
C = Chiusa
R = Rifiutata
*/
                if ( ( $row->chstat == 'C' || $row->chstat == 'R' ) && $status != 'closed' )
                {
                    $cli->warning( $status . ' -> ', false );
                    $cli->error( $row->chstat );
                    $cli->error( "  -> CORREGGERE" );
                }
                
                if ( $row->chstat == 'A'  && $status != 'read' )
                {
                    $cli->warning( $status . ' -> ', false );
                    $cli->error( $row->chstat );
                    $cli->error( "  -> CORREGGERE" );
                }
                
                if ( $row->chstat == 'K'  && $status != 'waiting' )
                {
                    $cli->warning( $status . ' -> ', false );
                    $cli->error( $row->chstat );
                    $cli->error( "  -> CORREGGERE" );
                }
                
            }
            catch( Exception $e )
            {                
                //$cli->warning( '? -> ', false );
                //$cli->error( $row->chstat );
            }
        }
        print_r( $states );
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}