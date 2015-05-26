<?php

class OpenpaSensorHelperFactory implements SensorHelperFactoryInterface
{
    /**
     * @param eZContentObject $contentObject
     *
     * @return SensorPostObjectHelperInterface
     */
    public function getSensorPostObjectHelper( eZContentObject $contentObject )
    {
        return OpenPAObjectHandler::instanceFromContentObject( $contentObject )->attribute( 'control_sensor' );
    }

    /**
     * @return string
     */
    public function getSensorCollaborationHandlerTypeString()
    {
        return 'openpasensor';
    }

    /**
     * @return array
     */
    public static function getSensorConfigParams()
    {
        return array(
            'DefaultPostExpirationDaysInterval' => OpenPAINI::variable( 'SensorConfig', 'DefaultPostExpirationDaysInterval', 15 ),
            'UniqueCategoryCount' => OpenPAINI::variable( 'SensorConfig', 'CategoryCount', 'unique' ) == 'unique',
            'CategoryAutomaticAssign' => OpenPAINI::variable( 'SensorConfig', 'CategoryAutomaticAssign', 'disabled' ) == 'enabled',
            'AuthorCanReopen' => OpenPAINI::variable( 'SensorConfig', 'AuthorCanReopen', 'disabled' ) == 'enabled',
            'CloseCommentsAfterSeconds' => OpenPAINI::variable( 'SensorConfig', 'CloseCommentsAfterSeconds', 1 )
        );
    }
}