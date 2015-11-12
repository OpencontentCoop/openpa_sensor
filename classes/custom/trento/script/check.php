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

        foreach( $doc->rows as $row )
        {
            $row = (array) $row;
            print_r($row);
        }
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}