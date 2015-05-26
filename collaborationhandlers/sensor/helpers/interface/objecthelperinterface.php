<?php

interface SensorPostObjectHelperInterface
{

    /**
     * @return eZContentObject
     */
    public function getContentObject();

    /**
     * @return eZContentObjectAttribute
     */
    public function getContentObjectAttribute( $identifier );

    public function setContentObjectAttribute( $identifier, $stringValue );

    public function makePrivate();

    public function makePublic();

    public function moderate( $identifier );

    public function setObjectState( $object, $status );

    /**
     * @return int[]
     */
    public function getApproverIdsByCategory();

    public function getType();

    public function getCurrentState();

    public function getCurrentPrivacyState();

    public function getCurrentModerationState();

    public function getPostGeoJsArray();

    public function getPostAuthorId();

    public function getPostAuthorName();

    public function getPostCategoryName();

    public function getApproverIdArray();

    public function getPostAreas();

    public function getPostCategories();

    public static function getOperators();

    /**
     * @return bool|null
     */
    public function defaultModerationStateIdentifier();
}