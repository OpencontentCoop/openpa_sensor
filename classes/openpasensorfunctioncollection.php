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
        return array( 'result' => self::recaptcha_get_html( $publicKey ), null, $useSSL );
    }

    private static function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false)
    {
        if ($pubkey == null || $pubkey == '') {
                die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
        }
        
        //$server = $use_ssl ? 'https:' : 'http:';
	$server = "https://www.google.com/recaptcha/api";
    
        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }
        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>
    
        <noscript>
                <iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
                <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
                <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
        </noscript>';
    }

    public static function fetchSurveyData( $contentObjectId, $userId )
    {
        try
        {
            $helper = SurveyHelper::instance( $contentObjectId );
            $helper->setUser( $userId );

            return array( 'result' => $helper );
        }
        catch( Exception $e )
        {
            return array( 'error' => $e->getMessage() );
        }
    }
} 
