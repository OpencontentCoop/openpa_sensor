<?php

interface SensorPostObjectHelperInterface
{

    /**
     * @return eZContentObject
     */
    public function getContentObject();

    /**
     * @param string $identifier
     *
     * @return mixed
     */
    public function getContentObjectAttribute( $identifier );

    /**
     * @param string $identifier
     * @param string $stringValue
     *
     * @return void
     */
    public function setContentObjectAttribute( $identifier, $stringValue );

    /**
     * @return void
     */
    public function makePrivate();

    /**
     * @return void
     */
    public function makePublic();

    /**
     * @param string $identifier
     *
     * @return void
     */
    public function moderate( $identifier );

    /**
     * @param eZContentObject $object
     * @param int $status
     *
     * @return mixed
     */
    public function setObjectState( $object, $status );

    /**
     * @return int[] eZUser ids
     */
    public function getApproverIdsByCategory();

    /**
     * @return int[] eZUser ids
     */
    public function getApproverIdArray();

    /**
     * @return eZContentObjectTreeNode[]
     */
    public static function getOperators();

    /**
     * Restituisce un hash con il valore dell'attributo type dell'oggetto corrente tradotto  o false
     * es: array( 'name' => 'Segnalazione', 'identifier' => 'segnalazione','css_class' => 'info' );
     * @return array|bool
     */
    public function getType();

    /**
     * Restituisce un array con nome identificatore e classcss del content object state di gruppo Sensor
     * es: array( 'name' => 'Foo', 'identifier' => 'bar' ,'css_class' => 'warning' );
     * @return array
     * @throws Exception
     */
    public function getCurrentState();

    /**
     * Restituisce un array con nome identificatore e classcss del content object state di gruppo Privacy
     * es: array( 'name' => 'Foo', 'identifier' => 'bar' ,'css_class' => 'warning' );
     * @return array
     * @throws Exception
     */
    public function getCurrentPrivacyState();

    /**
     * Restituisce un array con nome identificatore e classcss del content object state di gruppo Moderation
     * es: array( 'name' => 'Foo', 'identifier' => 'bar' ,'css_class' => 'warning' );
     * @return array
     * @throws Exception
     */
    public function getCurrentModerationState();

    /**
     * Ritorna il valore dell'attributo geo dell'oggetto corrente in formato javascript array
     * es: "[45.123,25.674]"
     * @return bool|string
     */
    public function getPostGeoJsArray();

    /**
     * Restituisce l'owner_id dell'oggetto corrente
     * @return int|null
     */
    public function getPostAuthorId();

    /**
     * Restituisce il name dell'owner dell'oggetto corrente
     * @return string
     */
    public function getPostAuthorName();

    /**
     * Restituisce il name delle categorie assegnate all'oggetto corrente
     * @return string
     */
    public function getPostCategoryName();

    /**
     * Restituisce un array tree
     * @see ObjectHandlerServiceControlSensor::walkSubtree
     * @return array
     */
    public function getPostAreas();

    /**
     * Restituisce un array tree
     * @see ObjectHandlerServiceControlSensor::walkSubtree
     * @return array
     */
    public function getPostCategories();

    /**
     * Restituisce l'url completo del post
     * @return string
     */
    public function getPostUrl();

    /**
     * Restituisce l'identificatore dello stato di moderazione di default
     * es: 'waiting'
     * @param SensorUserInfo $userInfo
     *
     * @return string|null
     */
    public function defaultModerationStateIdentifier( SensorUserInfo $userInfo = null );
}