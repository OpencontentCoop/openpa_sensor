<?php

$tpl = eZTemplate::factory();
$alerts = SensorUserInfo::current()->attribute( 'alerts' );
$tpl->setVariable( 'has_alerts', count( $alerts ) > 0 );
$tpl->setVariable( 'alerts', $alerts );
echo $tpl->fetch( 'design:sensor/parts/user_alert.tpl' );
eZExecution::cleanExit();