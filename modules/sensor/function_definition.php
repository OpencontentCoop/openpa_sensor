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

$FunctionList['items'] = array(
    'name' => 'items',
    'operation_types' => array( 'read' ),
    'call_method' => array(
        'class' => 'OpenPASensorFunctionCollection',
        'method' => 'fetchItems'
    ),
    'parameter_type' => 'standard',
    'parameters' => array(
        array(
            'name' => 'type',
            'type' => 'string',
            'required' => false,
            'default' => 'all'
        ),
        array(
            'name' => 'group_id',
            'type' => 'integer',
            'required' => false,
            'default' => false
        ),
        array(
            'name' => 'limit',
            'type' => 'integer',
            'required' => false,
            'default' => 10
        ),
        array(
            'name' => 'offset',
            'type' => 'integer',
            'required' => false,
            'default' => 0
        )
    )
);