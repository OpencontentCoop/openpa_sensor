<?php

$tpl = eZTemplate::factory();
echo $tpl->fetch( 'design:sensor/parts/user_alert.tpl' );
eZExecution::cleanExit();