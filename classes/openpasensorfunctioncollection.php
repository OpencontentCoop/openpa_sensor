<?php


class OpenPASensorFunctionCollection
{
    public static function fetchRecaptchaHTML()
    {
        require_once 'recaptchalib.php';
        $ini = eZINI::instance( 'ezcomments.ini' );
        $publicKey = $ini->variable( 'RecaptchaSetting', 'PublicKey' );
        $useSSL = false;
        if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] )
        {
            $useSSL = true;
        }
        return array( 'result' => recaptcha_get_html( $publicKey ), null, $useSSL );
    }
} 