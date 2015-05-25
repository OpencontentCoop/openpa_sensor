<?php
$FunctionList = array();

$FunctionList['recaptcha_html'] = array(
    'name' => 'recaptcha_html',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'class' => 'OpenPASensorFunctionCollection',
        'method' => 'fetchRecaptchaHTML'
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);

$FunctionList['survey_data'] = array(
    'name' => 'survey_data',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'class' => 'OpenPASensorFunctionCollection',
        'method' => 'fetchSurveyData'
    ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'contentobject_id',
            'required' => true
        ),
        array(
            'name' => 'user_id',
            'required' => false,
            'default' => false
        )
    )
);
