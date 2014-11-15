<?php
$module = $Params['Module'];
$view = empty( $Params['View'] ) ? 'home' : $Params['View'];

eZSys::clearAccessPath( false );
$module->redirectTo( 'sensor/' . $view );
return;