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
     * @param SensorUserInfo $user
     * @param $data
     *
     * @return eZContentObject
     */
    public function sensorPostObjectFactory( SensorUserInfo $user, $data, eZContentObject $update = null );

    /**
     * @return string
     */
    public function getSensorCollaborationHandlerTypeString();

    /**
     * @return array
     */
    public static function getSensorConfigParams();

    /**
     * @return int
     */
    public function getWhatsAppUserId();

}