<?php

interface SensorHelperFactoryInterface
{
    /**
     * @param eZContentObject $contentObject
     *
     * @return SensorPostObjectHelperInterface
     */
    public function getSensorPostObjectHelper( eZContentObject $contentObject );

    /**
     * @return string
     */
    public function getSensorCollaborationHandlerTypeString();

    /**
     * @return array
     */
    public static function getSensorConfigParams();

}