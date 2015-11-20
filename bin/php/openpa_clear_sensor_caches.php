<?php

include( 'autoload.php' );
$siteaccess = OpenPABase::getInstances( 'sensor' );
foreach( $siteaccess as $sa )
{
    $command = "php bin/php/ezcache.php --clear-id='sensor,template' -s$sa";
    print "Eseguo: $command \n";
    system( $command );
}

?>
