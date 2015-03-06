<?php

class ObjectHandlerServiceControlSensor extends ObjectHandlerServiceBase
{
    const SECTION_IDENTIFIER = "sensor";
    const SECTION_NAME = "Sensor";

    /**
     * @var eZContentObjectTreeNode
     */
    protected static $rootNode;

    // sensor/post
    /**
     * @var eZContentObjectTreeNode
     */
    protected static $postContainerNode;
    /**
     * @var eZContentObjectTreeNode
     */
    protected static $postCategoriesNode;
    protected static $postContentClass;
    protected static $postAreas;
    protected static $postCategories;

    public static $stateGroupIdentifier = 'sensor';
    public static $privacyStateGroupIdentifier = 'privacy';

    public static $stateIdentifiers = array(
        'pending' => "Inviato",
        'open' => "In carico",
        'close' => "Chiusa"
    );
    public static $privacyStateIdentifiers = array(
        'public' => "Pubblico",
        'private' => "Privato",
    );

    // sensor/forum
    /**
     * @var eZContentObjectTreeNode
     */
    protected static $forumContainerNode;
    protected static $forums;
    protected static $forumCommentClass;

    // sensor/survey
    /**
     * @var eZContentObjectTreeNode
     */
    protected static $surveyContainerNode;
    protected static $surveys;

    function run()
    {
        // general
        $this->fnData['site_title'] = 'getSiteTitle';
        $this->data['site_images'] = array(
            "apple-touch-icon-114x114-precomposed" => null,
            "apple-touch-icon-72x72-precomposed" => null,
            "apple-touch-icon-57x57-precomposed" => null,
            "favicon" => null
        );

        $this->fnData['logo'] = 'getLogo';
        $this->fnData['logo_title'] = 'getLogoTitle';
        $this->fnData['logo_subtitle'] = 'getLogoSubTitle';

        $this->fnData['banner'] = 'getBanner';
        $this->fnData['banner_title'] = 'getBannerTitle';
        $this->fnData['banner_subtitle'] = 'getBannerSubTitle';

        $this->fnData['footer'] = 'getFooter';
        $this->fnData['contacts'] = 'getContacts';

        $this->fnData['privacy'] = 'getPrivacy';
        $this->fnData['faq'] = 'getFaq';
        $this->fnData['terms'] = 'getTerms';

        $this->fnData['sensor_url'] = 'getSensorSiteaccessUrl';
        $this->fnData['sensor_asset_url'] = 'getAssetUrl';

        $this->data['post_is_enabled'] = self::PostIsEnable();
        $this->data['forum_is_enabled'] = self::ForumIsEnable();
        $this->data['survey_is_enabled'] = self::SurveyIsEnabled();

        // post
        $this->data['use_per_area_approver'] = false; //@todo impostare da ini?

        $this->fnData['helper'] = 'getHelper';

        $this->fnData['author_id'] = 'getAuthorId';
        $this->fnData['approver_id_array'] = 'getApproverIdArray';

        $this->fnData['geo_js_array'] = 'getGeoJsArray';

        $this->fnData['type'] = 'getType';
        $this->fnData['current_status'] = 'getCurrentStatus';
        $this->fnData['current_privacy_status'] = 'getCurrentPrivacyStatus';
        $this->fnData['current_owner'] = 'getCurrentOwner'; //@todo rimuovere dal service correggere chiamate tpl
        $this->fnData['comment_count'] = 'getCommentCount'; //@todo rimuovere dal service correggere chiamate tpl
        $this->fnData['response_count'] = 'getResponseCount'; //@todo rimuovere dal service correggere chiamate tpl

        $this->data['post_container_node'] = self::postContainerNode();
        $this->data['post_categories_container_node'] = self::postCategoriesNode();
        $this->data['post_class'] = self::postContentClass();
               
        $this->data['areas'] = self::postAreas();
        $this->data['categories'] = self::postCategories();
        $this->data['operators'] = self::operators();

        // forum
        $this->data['forum_container_node'] = self::forumContainerNode();
        $this->data['forums'] = self::forums();

        // survey
        $this->data['survey_container_node'] = self::surveyContainerNode();
        $this->data['surveys'] = self::surveys();
        $this->data['valid_surveys'] = self::validSurveys();
    }

    /**
     * Ritorna il valore dell'attributo post_enabled di rootNode
     * @return bool
     */
    public static function PostIsEnable()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return isset( $dataMap['post_enabled'] ) && $dataMap['post_enabled']->attribute( 'data_int' ) == 1;
    }

    /**
     * Ritorna il valore dell'attributo forum_enabled di rootNode
     * @return bool
     */
    public static function ForumIsEnable()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return isset( $dataMap['forum_enabled'] ) && $dataMap['forum_enabled']->attribute( 'data_int' ) == 1;
    }

    /**
     * Ritorna il valore dell'attributo survey_enabled di rootNode
     * @return bool
     */
    public static function SurveyIsEnabled()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return isset( $dataMap['survey_enabled'] ) && $dataMap['survey_enabled']->attribute( 'data_int' ) == 1;
    }

    /**
     * Ritorna l'indirizzo del sito sensor basandosi su site.ini[SiteSettings]SiteURL
     * @return string
     */
    protected function getAssetUrl()
    {
        $siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );       
        $parts = explode( '/', $siteUrl );        
        if ( count( $parts ) >= 2 )
        {
            $suffix = array_pop( $parts );
            $siteUrl = implode( '/', $parts );
        }        
        return rtrim( $siteUrl, '/' );
    }

    /**
     * Ritorna l'indirizzo del sito sensor basandosi su site.ini[SiteSettings]SiteURL
     * @see OpenPABase::getCustomSiteaccessName
     * @return string
     */
    protected function getSensorSiteaccessUrl()
    {
        $sitaccessIdentifier = OpenPABase::getCustomSiteaccessName( 'sensor' );
        $path = "settings/siteaccess/{$sitaccessIdentifier}/";
        $ini = new eZINI( 'site.ini.append', $path, null, null, null, true, true );        
        if ( $ini->hasVariable( 'SiteSettings', 'SiteURL' ) )
            return rtrim( $ini->variable( 'SiteSettings', 'SiteURL' ), '/' );
        else
        {
            return $this->getAssetUrl();
        }
    }

    /**
     * Ritorna l'attributo privacy di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getPrivacy()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return $dataMap['privacy'];
    }

    /**
     * Ritorna l'attributo faq di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getFaq()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return $dataMap['faq'];
    }

    /**
     * Ritorna l'attributo terms di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getTerms()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return $dataMap['terms'];
    }

    /**
     * Invoca il SensorHelper per l'oggetto corrente
     * @return null|SensorHelper
     * @throws Exception
     */
    protected function getHelper()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
            return SensorHelper::instanceFromContentObjectId( $this->container->getContentObject()->attribute( 'id' ) );
        return null;
    }

    /**
     * Restituisce l'owner_id dell'oggetto corrente
     * @return int|null
     */
    protected function getAuthorId()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
            return $this->container->getContentObject()->attribute( 'owner_id' );
        return null;
    }

    /**
     * Restituisce un array di id eZUser che vengono impostati come primi approvatori della richiesta
     * Se use_per_area_approver == true cerca l'utente in base all'area
     * Altrimenti restituisce gli utenti valorizzati nell'attributo approver della prima sensor_area
     *
     * @return int[]
     */
    protected function getApproverIdArray()
    {
        $data = array();
        if ( $this->attribute( 'use_per_area_approver' ) )
        {
            if ( $this->container->hasAttribute( 'area' ) )
            {
                $areaRelationList = explode(
                    '-',
                    $this->container->attribute( 'area' )->attribute(
                        'contentobject_attribute'
                    )->toString()
                );
                foreach ( $areaRelationList as $item )
                {
                    $area = eZContentObject::fetch( $item );
                    $areaDataMap = $area->attribute( 'data_map' );
                    if ( isset( $areaDataMap['approver'] ) )
                    {
                        $data = explode( '-', $areaDataMap['approver']->toString() );
                        break;
                    }
                }
            }
        }
        if ( empty( $data ) )
        {
            $areas = self::postAreas();
            $area = isset( $areas['tree'][0]['node'] ) ? $areas['tree'][0]['node'] : false;
            if ( $area instanceof eZContentObjectTreeNode )
            {
                $areaDataMap = $area->attribute( 'data_map' );
                if ( isset( $areaDataMap['approver'] ) )
                {
                    $data = explode( '-', $areaDataMap['approver']->toString() );
                }
            }
        }

        return $data;
    }

    /**
     * Ritorna il valore dell'attributo geo dell'oggetto corrente in formato javascript array
     * @return bool|string
     */
    protected function getGeoJsArray()
    {
        $data = false;
        if ( $this->container->hasAttribute( 'geo' )
             &&  $this->container->attribute( 'geo' )->attribute( 'has_content' ) )
        {
            $content = $this->container->attribute( 'geo' )->attribute(
                'contentobject_attribute'
            )->content();
            $data = "[{$content->attribute( 'latitude' )},{$content->attribute( 'longitude' )}]";
        }
        return $data;
    }

    /**
     * Restituisce un hash con il valore dell'attributo type dell'oggetto corrente tradotto
     * @return array|bool
     */
    protected function getType()
    {
        $data = false;
        if ( $this->container->hasAttribute( 'type' )
             &&  $this->container->attribute( 'type' )->attribute( 'has_content' ) )
        {
            $content = $this->container->attribute( 'type' )->attribute(
                'contentobject_attribute'
            )->toString();
            if ( $content == 'segnalazione' )
            {
                $data = array(
                    'name' => ezpI18n::tr( 'openpa_sensor/type', 'Segnalazione' ),
                    'identifier' => 'segnalazione',
                    'css_class' => 'info'
                );
            }
            elseif ( $content == 'suggerimento' )
            {
                $data = array(
                    'name' => ezpI18n::tr( 'openpa_sensor/type', 'Suggerimento' ),
                    'identifier' => 'suggerimento',
                    'css_class' => 'warning'
                );
            }
            elseif ( $content == 'reclamo' )
            {
                $data = array(
                    'name' => ezpI18n::tr( 'openpa_sensor/type', 'Reclamo' ),
                    'identifier' => 'reclamo',
                    'css_class' => 'danger'
                );
            }
        }
        return $data;
    }

    //@todo rimuovere dal service correggere chiamate tpl
    protected function getCommentCount()
    {
        return $this->getHelper()->attribute( 'public_message_count' );
    }

    //@todo rimuovere dal service correggere chiamate tpl
    protected function getResponseCount()
    {
        return $this->getHelper()->attribute( 'response_message_count' );
    }

    //@todo rimuovere dal service correggere chiamate tpl
    protected function getCurrentOwner()
    {
        return $this->getHelper()->attribute( 'current_owner' );

    }

    /**
     * Restituisce un array con nome identificatoe e classcss del content object state di gruppo Privacy
     * @return array
     * @throws Exception
     */
    protected function getCurrentPrivacyStatus()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
        {
            $states = OpenPABase::initStateGroup(
                self::$privacyStateGroupIdentifier,
                self::$privacyStateIdentifiers
            );
            foreach ( $states as $state )
            {
                if ( in_array( $state->attribute( 'id' ), $this->container->getContentObject()->attribute( 'state_id_array' ) ) )
                {
                    return array(
                        'name' => $state->attribute( 'current_translation' )->attribute( 'name' ),
                        'identifier' => $state->attribute( 'identifier' ),
                        'css_class' => $state->attribute( 'identifier' ) == 'private' ? 'default' : 'info'
                    );
                }
            }
        }
        return array();
    }

    /**
     * Restituisce un array con nome identificatoe e classcss del content object state di gruppo Sensor
     * @return array
     * @throws Exception
     */
    protected function getCurrentStatus()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
        {
            $states = OpenPABase::initStateGroup(
                self::$stateGroupIdentifier,
                self::$stateIdentifiers
            );
            foreach ( $states as $state )
            {
                $cssClass = 'info';
                if ( $state->attribute( 'identifier' ) == 'pending' )
                {
                    $cssClass = 'danger';
                }
                elseif ( $state->attribute( 'identifier' ) == 'open' )
                {
                    $cssClass = 'warning';
                }
                elseif ( $state->attribute( 'identifier' ) == 'close' )
                {
                    $cssClass = 'success';
                }
                if ( in_array( $state->attribute( 'id' ), $this->container->getContentObject()->attribute( 'state_id_array' ) ) )
                {
                    return array(
                        'name' => $state->attribute( 'current_translation' )->attribute( 'name' ),
                        'identifier' => $state->attribute( 'identifier' ),
                        'css_class' => $cssClass
                    );
                }
            }
        }
        return array();
    }

    /**
     * Ritorna l'attributo footer di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getFooter()
    {
        $data = '';
        if ( $this->container->hasAttribute( 'footer' ) )
        {
            $attribute = $this->container->attribute( 'footer' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute )
            {
                $data = $attribute;
            }
        }
        return $data;
    }

    /**
     * Ritorna l'attributo contacts di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getContacts()
    {
        $data = '';
        if ( $this->container->hasAttribute( 'contacts' ) )
        {
            $attribute = $this->container->attribute( 'contacts' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute )
            {
                $data = $attribute;
            }
        }
        return $data;
    }

    /**
     * Ritorna il full_path dell'immagine banner di rootNode
     * @return string
     */
    protected function getBanner()
    {
        $data = false;
        if ( $this->container->hasAttribute( 'banner' ) )
        {
            $attribute = $this->container->attribute( 'banner' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute && $attribute->hasContent() )
            {
                $content = $attribute->content()->attribute( 'original' );
                $data = $content['full_path'];
            }
        }
        return $data;
    }

    /**
     * Restituisce il valore stringa dell'attributo banner_title
     * @return string
     */
    protected function getBannerTitle()
    {
        return $this->getAttributeString( 'banner_title' );
    }

    /**
     * Restituisce il valore stringa dell'attributo banner_subtitle
     * @return string
     */
    protected function getBannerSubTitle()
    {
        return $this->getAttributeString( 'banner_subtitle' );
    }

    /**
     * Ritorna il full_path dell'immagine logo di rootNode
     * @return string
     */
    protected function getLogo()
    {
        $data = false;
        if ( $this->container->hasAttribute( 'logo' ) )
        {
            $attribute = $this->container->attribute( 'logo' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute && $attribute->hasContent() )
            {
                $content = $attribute->content()->attribute( 'original' );
                $data = $content['full_path'];
            }
            else
            {
                $data = '/extension/openpa_sensor/design/standard/images/logo_sensor.png';
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    protected function getSiteTitle()
    {
        return strip_tags( $this->getLogoTitle() );
    }

    /**
     * @return string
     */
    protected function getLogoTitle()
    {
        return $this->getAttributeString( 'logo_title' );
    }

    /**
     * @return string
     */
    protected function getLogoSubTitle()
    {
        return $this->getAttributeString( 'logo_subtitle' );
    }

    /**
     * @return string
     */
    protected function getAttributeString( $identifier )
    {
        $data = '';
        if ( $this->container->hasAttribute( $identifier ) )
        {
            $attribute = $this->container->attribute( $identifier )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute )
            {
                $data = self::replaceBracket( $attribute->toString() );
            }
        }
        return $data;
    }

    /**
     * Replace [ ] with strong html tag
     * @return string
     */
    public static function replaceBracket( $string )
    {
        $string = str_replace( '[', '<strong>', $string );
        $string = str_replace( ']', '</strong>', $string );
        return $string;
    }

    /**
     * Inizializza classi, gruppi e sezioni per l'utilizzo di Sensor
     *
     * @param array $options
     * @return void
     */
    public static function init( $options = array() )
    {
        $installer = new OpenPASensorInstaller();
        $installer->beforeInstall( $options );
        $installer->install();
        $installer->afterInstall();
    }

    /**
     * Remote id di rootNode
     * @return string
     */
    public static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }

    /**
     * @return eZContentObjectTreeNode|null
     */
    public static function rootNode()
    {
        if ( self::$rootNode == null )
        {
            if ( !isset( $GLOBALS['SensorRootNode'] ) )
            {
                $root =eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
                if ( $root instanceof eZContentObject )
                {
                    $GLOBALS['SensorRootNode'] = $root->attribute( 'main_node' );
                }
            }            
            self::$rootNode = $GLOBALS['SensorRootNode'];
        }
        return self::$rootNode;
    }

    /**
     * @return eZContentObjectTreeNode|null
     */
    public static function postCategoriesNode()
    {
        if ( self::$postCategoriesNode == null )
        {
            $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_postcategories' );
            if ( $root instanceof eZContentObject )
            {
                self::$postCategoriesNode = $root->attribute( 'main_node' );
            }
            else
            {
                self::$postCategoriesNode = self::rootNode();;
            }
        }
        return self::$postCategoriesNode;
    }

    /**
     * @return eZContentObjectTreeNode|null
     */
    public static function forumContainerNode()
    {
        if ( self::$forumContainerNode == null )
        {
            $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_dimmi' );
            if ( $root instanceof eZContentObject )
            {
                self::$forumContainerNode = $root->attribute( 'main_node' );
            }
        }
        return self::$forumContainerNode;
    }

    /**
     * @return eZContentObjectTreeNode|null
     */
    public static function surveyContainerNode()
    {
        if ( self::$surveyContainerNode == null )
        {
            $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_survey' );
            if ( $root instanceof eZContentObject )
            {
                self::$surveyContainerNode = $root->attribute( 'main_node' );
            }
            else
            {
                self::$surveyContainerNode = self::rootNode();;
            }
        }
        return self::$surveyContainerNode;
    }

    /**
     * @return eZContentObjectTreeNode|null
     */
    public static function postContainerNode()
    {
        if ( self::$postContainerNode == null )
        {
            $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() . '_postcontainer' );
            if ( $root instanceof eZContentObject )
            {
                self::$postContainerNode = $root->attribute( 'main_node' );
            }
            else
            {
                self::$postContainerNode = self::rootNode();;
            }
        }
        return self::$postContainerNode;
    }

    /**
     * @return eZContentClass|null
     */
    public static function postContentClass()
    {
        if ( self::$postContentClass == null )
        {
            self::$postContentClass = eZContentClass::fetchByIdentifier( 'sensor_post' );
        }
        return self::$postContentClass;
    }

    /**
     * @return eZContentClass|null
     */
    public static function forumCommentClass()
    {
        if ( self::$forumCommentClass == null )
        {
            self::$forumCommentClass = eZContentClass::fetchByIdentifier( 'dimmi_forum_reply' );
        }
        return self::$forumCommentClass;
    }

    /**
     * @return eZContentObjectTreeNode[]
     */
    public static function operators()
    {
        return self::rootNode()->subTree( array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => array( 'user', 'sensor_operator', 'dipendente' ),
            'SortBy' => array( 'name', true )
        ) );
    }

    /**
     * Restituisce un array tree
     * @see self::walkSubtree
     * @return array
     */
    public static function postAreas()
    {
        if ( self::$postAreas == null )
        {
            $data = $coords = array();
            /** @var eZContentObjectTreeNode[] $treeAreas */
            $treeAreas = self::rootNode()->subTree( array(
                    'ClassFilterType' => 'include',
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'ClassFilterArray' => array( 'sensor_area' ),
                    'Limitation' => array(),
                    'SortBy' => array( 'name', true )
                ) );

            foreach( $treeAreas as $node )
            {
                self::findAreaCoords( $node->attribute( 'object' ), $coords );
                $data[] = array(
                    'node' => $node,
                    'children' => self::walkSubtree( $node, $coords )
                );
            }

            self::$postAreas = array( 'tree' => $data, 'coords_json' => json_encode( $coords ), 'coords', $coords );
        }
        return self::$postAreas;
    }

    /**
     * Restituisce un array tree
     * @see self::walkSubtree
     * @return array
     */
    public static function postCategories()
    {
        if ( self::$postCategories == null )
        {
            $data = array();
            $false = false;
            /** @var eZContentObjectTreeNode[] $treeCategories */
            $treeCategories = self::postCategoriesNode()->subTree( array(
                    'ClassFilterType' => 'include',
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'ClassFilterArray' => array( 'sensor_category' ),
                    'Limitation' => array(),
                    'SortBy' => array( 'name', true )
                ) );

            foreach( $treeCategories as $node )
            {                
                $data[] = array(
                    'node' => $node,
                    'children' => self::walkSubtree( $node, $false )
                );
            }

            self::$postCategories = array( 'tree' => $data );
        }
        return self::$postCategories;
    }

    /**
     * @return array
     */
    public static function surveys()
    {
        if ( self::$surveys == null )
        {
            $data = array();
            $false = false;
            $includeClasses = array( 'consultation_survey' );
            /** @var eZContentObjectTreeNode[] $treeCategories */
            $surveys = self::surveyContainerNode()->subTree( array(
                'ClassFilterType' => 'include',
                'Depth' => 1,
                'DepthOperator' => 'eq',
                'ClassFilterArray' => $includeClasses,
                'Limitation' => array(),
                'SortBy' => array( 'name', true )
            ) );

            foreach( $surveys as $survey )
            {
                /** @var eZContentObject $surveyObject */
                $surveyObject = $survey->attribute( 'object' );
                /** @var eZContentObjectAttribute[] $surveyAttributes */
                $surveyAttributes = $surveyObject->fetchAttributesByIdentifier( array( 'survey' ) );
                if ( count( $surveyAttributes ) )
                {
                    $surveyAttribute = array_shift( $surveyAttributes );
                    $surveyAttributeContent = $surveyAttribute->content();
                    self::$surveys[] = array(
                        'node' => $survey,
                        'object' => $surveyObject,
                        'survey_attribute' => $surveyAttribute,
                        'survey_content' => $surveyAttributeContent
                    );
                }
            }
        }
        return self::$surveys;
    }

    /**
     * @return array
     */
    public static function validSurveys()
    {
        $data = array();
        foreach( self::surveys() as $item )
        {
            if ( $item['survey_content']['survey']->attribute( 'enabled' ) )
            {
                $data[] = $item;
            }
        }
        return $data;
    }

    /**
     * @see self::walkSubtree
     * @return array
     */
    public static function forums()
    {
        if ( self::$forums == null )
        {
            $data = array();
            $false = false;
            $includeClasses = array( 'dimmi_forum', 'dimmi_forum_topic' );
            /** @var eZContentObjectTreeNode[] $treeCategories */
            $tree = self::forumContainerNode()->subTree( array(
                'ClassFilterType' => 'include',
                'Depth' => 1,
                'DepthOperator' => 'eq',
                'ClassFilterArray' => $includeClasses,
                'Limitation' => array(),
                'SortBy' => array( 'name', true )
            ) );

            foreach( $tree as $node )
            {
                $data[] = array(
                    'node' => $node,
                    'children' => self::walkSubtree( $node, $false, $includeClasses )
                );
            }

            self::$forums = array( 'tree' => $data );
        }
        return self::$forums;
    }

    protected static function walkSubtree( eZContentObjectTreeNode $node, &$coords, $includeClasses = array() )
    {
        $data = array();
        if ( $node->childrenCount() > 0 )
        {
            if ( empty( $includeClasses ) )
            {
                $children = $node->children();
            }
            else
            {
                $children = $node->subTree( array(
                    'ClassFilterType' => 'include',
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'ClassFilterArray' => $includeClasses,
                    'Limitation' => array(),
                    'SortBy' => $node->attribute( 'sort_array' )
                ) );
            }
            foreach( $children as $subNode )
            {
                if ( is_array( $coords ) )
                {
                    self::findAreaCoords( $subNode->attribute( 'object' ), $coords, $includeClasses );
                }
                $data[] = array(
                    'node' => $subNode,
                    'children' => self::walkSubtree( $subNode, $coords, $includeClasses )
                );
            }
        }
        return $data;
    }

    protected  static function findAreaCoords( eZContentObject $area, &$coords )
    {
        $dataMap = $area->attribute( 'data_map' );
        if ( isset( $dataMap['geo'] ) && $dataMap['geo']->hasContent() )
        {
            /** @var eZGmapLocation $content */
            $content = $dataMap['geo']->content() ;
            $data = array( 'lat' => $content->attribute( 'latitude' ), 'lng' => $content->attribute( 'longitude' ) );
            $coords[] = array( 'id' => $area->attribute( 'id' ), 'coords' => array( $data['lat'], $data['lng'] ) );
        }
    }

    /**
     * @param eZContentObject $object
     * @param $stateGroup
     * @param $stateIdentifier
     *
     * @throws Exception
     */
    public static function setState( eZContentObject $object, $stateGroup, $stateIdentifier )
    {
        $states = array();
        if ( $stateGroup == 'privacy' )
            $states = OpenPABase::initStateGroup( self::$privacyStateGroupIdentifier, self::$privacyStateIdentifiers );
        elseif ( $stateGroup == 'sensor' )
            $states = OpenPABase::initStateGroup( self::$stateGroupIdentifier, self::$stateIdentifiers );

        $state = $states[$stateGroup . '.' . $stateIdentifier];
        if ( $state instanceof eZContentObjectState )
        {
            if ( eZOperationHandler::operationIsAvailable( 'content_updateobjectstate' ) )
            {
                eZOperationHandler::execute( 'content', 'updateobjectstate',
                    array( 'object_id' => $object->attribute( 'id' ),
                           'state_id_list' => array( $state->attribute( 'id' ) ) ) );
            }
            else
            {
                eZContentOperationCollection::updateObjectState( $object->attribute( 'id' ), array( $state->attribute( 'id' ) ) );
            }
        }
    }

    /**
     * @param array $parameters
     * @param eZProcess $process
     * @param eZWorkflowEvent $event
     *
     * @throws Exception
     */
    public static function executeWorkflow( $parameters, $process, $event )
    {
        $trigger = $parameters['trigger_name'];
        if ( $trigger == 'pre_read' )
        {
            $redirectUrl = $redirectUrlAlias = false;
            $currentSiteaccess = eZSiteAccess::current();
            if ( OpenPABase::getCustomSiteaccessName( 'sensor' ) != $currentSiteaccess['name']
                 && OpenPABase::getBackendSiteaccessName() != $currentSiteaccess['name'] )
            {
                $nodeId = $parameters['node_id'];
                $node = eZContentObjectTreeNode::fetch( $nodeId );
                if ( $node instanceof eZContentObjectTreeNode )
                {
                    if ( in_array( $node->attribute( 'class_identifier' ), OpenPASensorInstaller::sensorClassIdentifiers() ) )
                    {
                        if ( $node->attribute( 'class_identifier' ) == 'dimmi_forum_reply' )
                        {
                            $redirectUrlAlias = $node->attribute( 'parent' )->attribute( 'url_alias' );                            
                        }
                        else
                        {
                            $redirectUrlAlias = $node->attribute( 'url_alias' );
                        }
                    }
                    
                    if ( $redirectUrlAlias )
                    {
                        $sensorSA = OpenPABase::getCustomSiteaccessName( 'sensor' );
                        $path = "settings/siteaccess/{$sensorSA}/";
                        $iniFile = "site.ini";
                        $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );  
                        $redirectUrl = 'http://' . $ini->variable( 'SiteSettings', 'SiteURL' ) . '/' . $redirectUrlAlias;
                    }
                }
            }
            
            if ( $redirectUrl )
            {
                eZDebug::writeNotice($redirectUrl);
                header( 'Location: ' . $redirectUrl );
            }
        }
        elseif ( $trigger == 'post_publish' )
        {
            $id = $parameters['object_id'];
            $object = eZContentObject::fetch( $id );
            if ( $object instanceof eZContentObject
                 && $object->attribute( 'class_identifier' ) == 'sensor_post'
                 && $object->attribute( 'current_version') == 1 )
            {
                SensorHelper::createCollaborationItem( $id );
                $dataMap = $object->attribute( 'data_map' );
                if ( isset( $dataMap['privacy'] ) )
                {
                    if ( $dataMap['privacy']->attribute( 'data_int' ) == 0 )
                    {
                        OpenPABase::sudo( function() use( $object ){
                            ObjectHandlerServiceControlSensor::setState( $object, 'privacy', 'private' );
                        });
                    }
                }
            }
            elseif ( $object instanceof eZContentObject
                 && $object->attribute( 'class_identifier' ) == 'sensor_root'  )
            {                
                eZCache::clearByTag( 'template' );
            }
        }
        elseif ( $trigger == 'pre_delete' )
        {
            $nodeIdList = $parameters['node_id_list'];
            $inTrash = (bool) $parameters['move_to_trash'];
            foreach( $nodeIdList as $nodeId )
            {
                $object = eZContentObject::fetchByNodeID( $nodeId );
                if ( $object instanceof eZContentObject )
                {
                    try
                    {
                        $helper = SensorHelper::instanceFromContentObjectId( $object->attribute( 'id' ) );
                        if ( $inTrash )
                            $helper->moveToTrash();
                        else
                            $helper->delete();
                    }
                    catch( Exception $e )
                    {
                        
                    }
                }
            }
        }
    }

    /**
     * @param bool $asObject
     *
     * @return array|eZContentObjectTreeNode[]
     */
    public static function fetchPosts( $asObject = false )
    {
        $solrFetchParams = array(
            'SearchOffset' => 0,
            'SearchLimit' => 1000,
            'Facet' => null,
            'SortBy' => array( 'published' => 'desc' ),
            'Filter' => array( 'attr_privacy_b:1' ),
            'SearchContentClassID' => array( 'sensor_post' ),
            'SearchSectionID' => null,
            'SearchSubTreeArray' => array( self::postContainerNode()->attribute( 'node_id' ) ),
            'AsObjects' => $asObject,
            'SpellCheck' => null,
            'IgnoreVisibility' => null,
            'Limitation' => null,
            'BoostFunctions' => null,
            'QueryHandler' => 'ezpublish',
            'EnableElevation' => true,
            'ForceElevation' => true,
            'SearchDate' => null,
            'DistributedSearch' => null,
            'FieldsToReturn' => array(
                'subattr_geo___coordinates____gpt',
                'attr_type_s',
                'attr_subject_t',
                'subattr_category___name____s',
                'meta_object_states_si'
            ),
            'SearchResultClustering' => null,
            'ExtendedAttributeFilter' => array()
        );        
        $solrSearch = new eZSolr();
        eZINI::instance( 'ezfind.ini' )->setVariable( 'LanguageSearch', 'SearchMainLanguageOnly', 'disabled' );
        $solrResult = $solrSearch->search( '', $solrFetchParams );        
        return $solrResult;
    }

    /**
     * @return SensorGeoJsonFeatureCollection
     */
    public static function fetchSensorGeoJsonFeatureCollection()
    {
        $data = new SensorGeoJsonFeatureCollection();
        $items = self::fetchPosts( false );
        foreach( $items['SearchResult'] as $item )
        {
            $geo = isset( $item['fields']['subattr_geo___coordinates____gpt'] ) ? $item['fields']['subattr_geo___coordinates____gpt'] : array();            
            if ( count( $geo ) > 0 )
            {
                $geometryArray = explode( ',', $geo[0] );
                
                $id = $item['id_si'];
                $type = isset( $item['fields']['attr_type_s'] ) ? $item['fields']['attr_type_s'] : false;
                $name = isset( $item['fields']['attr_subject_t'] ) ? $item['fields']['attr_subject_t'] : false;
                
                $properties = array(
                    'type' => $type,
                    'name' => $name,
                    'popupContent' => '<em>Loading...</em>'
                );
                $feature = new SensorGeoJsonFeature( $id, $geometryArray, $properties );
                $data->add( $feature );
            }                
        }
        return $data;
    }
}