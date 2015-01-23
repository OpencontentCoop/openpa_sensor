<?php
$module = $Params['Module'];
$view = empty( $Params['View'] ) ? 'home' : $Params['View'];
$view = explode( ',', $view );
eZSys::clearAccessPath( false );
$module->redirectTo( 'sensor/' . implode( '/', $view ) );
return;