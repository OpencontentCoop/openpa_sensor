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

    /**
     * @return int[]
     */
    public function getApproverIdArray();

    public static function getOperators();

    public function getType();

    public function getCurrentState();

    public function getCurrentPrivacyState();

    public function getCurrentModerationState();

    public function getPostGeoJsArray();

    public function getPostAuthorId();

    public function getPostAuthorName();

    public function getPostCategoryName();

    public function getPostAreas();

    public function getPostCategories();

    public function getPostUrl();

    /**
     * @return bool|null
     */
    public function defaultModerationStateIdentifier( SensorUserInfo $userInfo = null );
}