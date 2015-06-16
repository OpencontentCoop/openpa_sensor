<?php

class OpenPASensorType extends eZWorkflowEventType
{

    const WORKFLOW_TYPE_STRING = 'openpasensor';

    function __construct()
    {
        $this->eZWorkflowEventType(
            self::WORKFLOW_TYPE_STRING,
            ezpI18n::tr( 'openpa/workflow/event', 'Workflow Sensor' )
        );
    }

    /**
     * @param eZWorkflowProcess $process
     * @param eZEvent $event
     *
     * @return int
     */
    function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );

        try
        {
            ObjectHandlerServiceControlSensor::executeWorkflow( $parameters, $process, $event );
            return eZWorkflowType::STATUS_ACCEPTED;
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
            return eZWorkflowType::STATUS_REJECTED;
        }

    }
}

eZWorkflowEventType::registerEventType( OpenPASensorType::WORKFLOW_TYPE_STRING, 'OpenPASensorType' );
