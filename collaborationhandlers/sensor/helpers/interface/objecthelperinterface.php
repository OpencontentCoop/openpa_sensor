<?php

interface SensorPostObjectHelperInterface
{

    public function getContentObject();

    public function getContentObjectAttribute( $identifier );

    public function setContentObjectAttribute( $identifier, $stringValue );

    public function makePrivate();

    public function makePublic();

    public function moderate( $identifier );

    public function setObjectState( $object, $status );

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
}