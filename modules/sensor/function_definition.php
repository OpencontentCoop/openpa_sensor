<?php
$FunctionList = array();
$FunctionList['recaptcha_html'] = array( 'name' => 'recaptcha_html',
                                         'operation_types' => array( 'read' ),
                                         'call_method' => array(
                                             'class' => 'OpenPASensorFunctionCollection',
                                             'method' => 'fetchRecaptchaHTML'
                                         ),
                                         'parameter_type' => 'standard',
                                         'parameters' => array() );