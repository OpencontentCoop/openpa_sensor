<?php

class ObjectHandlerServiceControlSensor extends ObjectHandlerServiceBase implements SensorPostObjectHelperInterface, SensorHelperFactoryInterface, OCPageDataHandlerInterface
{
    const SECTION_IDENTIFIER = "sensor";
    const SECTION_NAME = "Sensor";

    /**
     * @var eZContentObjectTreeNode
     */
    protected static $rootNode;

    /**
     * @var eZContentObjectAttribute[]
     */
    protected static $rootNodeDataMap;

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
    public static $stateIdentifiers = array(
        'pending' => "Inviato",
        'open' => "In carico",
        'close' => "Chiusa"
    );

    public static $privacyStateGroupIdentifier = 'privacy';
    public static $privacyStateIdentifiers = array(
        'public' => "Pubblico",
        'private' => "Privato",
    );

    public static $moderationStateGroupIdentifier = 'moderation';
    public static $moderationStateIdentifiers = array(
        'skipped' => "Non necessita di moderazione",
        'waiting' => "In attesa di moderazione",
        'accepted' => "Accettato",
        'refused' => "Rifiutato"
    );

    function run()
    {
        $this->data['moderation_is_enabled'] = self::ModerationIsEnabled();
        $this->data['timed_moderation_is_enabled'] = self::TimedModerationIsEnabled();
        $this->data['use_per_area_approver'] = false; //@todo impostare da ini?
        $this->fnData['post_container_node'] = 'postContainerNode';
        $this->fnData['post_categories_container_node'] = 'postCategoriesNode';
        $this->fnData['post_class'] = 'postContentClass';
        $this->fnData['areas'] = 'areas';
        $this->fnData['categories'] = 'categories';
        $this->fnData['operators'] = 'operators';
        $this->fnData['privacy'] = 'getPrivacy';
        $this->fnData['faq'] = 'getFaq';
        $this->fnData['terms'] = 'getTerms';
        $this->fnData['cookie'] = 'getCookie';
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
     * Ritorna l'attributo privacy di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getPrivacy()
    {
        $dataMap = self::rootNodeDataMap();
        return $dataMap['privacy'];
    }

    /**
     * Ritorna l'attributo faq di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getFaq()
    {
        $dataMap = self::rootNodeDataMap();
        return $dataMap['faq'];
    }

    /**
     * Ritorna l'attributo terms di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getTerms()
    {
        $dataMap = self::rootNodeDataMap();
        return $dataMap['terms'];
    }

    /**
     * Ritorna l'attributo cookie di rootNode
     * @return eZContentObjectAttribute
     */
    protected function getCookie()
    {
        $dataMap = self::rootNodeDataMap();
        return $dataMap['cookie'];
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
                $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
                if ( $root instanceof eZContentObject )
                {
                    $GLOBALS['SensorRootNode'] = $root->attribute( 'main_node' );
                }
            }            
            self::$rootNode = $GLOBALS['SensorRootNode'];
        }
        return self::$rootNode;
    }
    
    public static function rootNodeDataMap()
    {
        if ( self::$rootNodeDataMap == null )
        {
            $node = self::rootNode();
            self::$rootNodeDataMap = $node->attribute( 'data_map' );
        }
        return self::$rootNodeDataMap;
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
            /** @var eZContentObjectTreeNode[] $children */
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

    protected static function findAreaCoords( eZContentObject $area, &$coords )
    {
        /** @var eZContentObjectAttribute[] $dataMap */
        $dataMap = $area->attribute( 'data_map' );
        if ( isset( $dataMap['geo'] ) && $dataMap['geo']->hasContent() )
        {
            /** @var eZGmapLocation $content */
            $content = $dataMap['geo']->content() ;
            $data = array( 'lat' => $content->attribute( 'latitude' ), 'lng' => $content->attribute( 'longitude' ) );
            $coords[] = array( 'id' => $area->attribute( 'id' ), 'coords' => array( $data['lat'], $data['lng'] ) );
        }
    }

    public static function isSensorSiteAccessName( $currentSiteAccessName )
    {
        return OpenPABase::getCustomSiteaccessName( 'sensor' ) == $currentSiteAccessName;
    }

    public static function getSensorSiteAccessName()
    {
        return OpenPABase::getCustomSiteaccessName( 'sensor' );
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
        elseif ( $stateGroup == 'moderation' )
            $states = OpenPABase::initStateGroup( self::$moderationStateGroupIdentifier, self::$moderationStateIdentifiers );

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
        eZDebug::writeNotice( "Sensor workflow for $trigger", __METHOD__ );
        if ( $trigger == 'pre_read' )
        {
            $redirectUrl = $redirectUrlAlias = false;
            $currentSiteaccess = eZSiteAccess::current();
            if ( !self::isSensorSiteAccessName( $currentSiteaccess['name'] )
                 && OpenPABase::getBackendSiteaccessName() != $currentSiteaccess['name'] )
            {
                $nodeId = $parameters['node_id'];
                $node = eZContentObjectTreeNode::fetch( $nodeId );
                if ( $node instanceof eZContentObjectTreeNode )
                {
                    if ( in_array( $node->attribute( 'class_identifier' ), OpenPASensorInstaller::sensorClassIdentifiers() ) )
                    {
                        $sensorSA = self::getSensorSiteAccessNameByClassIdentifier( $node->attribute( 'class_identifier' ) );
                        if ( $node->attribute( 'class_identifier' ) == 'dimmi_forum_reply' )
                        {
                            $parent = $node->attribute( 'parent' );
                            if ( $parent instanceof eZContentObjectTreeNode )
                            {
                                $redirectUrlAlias = $parent->attribute( 'url_alias' );
                            }
                        }
                        else
                        {
                            $redirectUrlAlias = $node->attribute( 'url_alias' );
                        }
                        if ( $redirectUrlAlias )
                        {
                            $path = "settings/siteaccess/{$sensorSA}/";
                            $iniFile = "site.ini";
                            $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );
                            $redirectUrl = 'http://' . $ini->variable( 'SiteSettings', 'SiteURL' ) . '/' . $redirectUrlAlias;
                        }
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
            if ( $object instanceof eZContentObject )
            {
                if ( $object->attribute( 'class_identifier' ) == 'sensor_post' )
                {
                    if ( $object->attribute( 'current_version') == 1  )
                    {
                        try
                        {
                            SensorHelper::createSensorPost( $object );
                            eZSearch::addObject( $object, true );
                        }
                        catch( Exception $e )
                        {
                            eZDebug::writeError( $e->getMessage(), __METHOD__ );
                        }
                    }
                    else
                    {
                        try
                        {
                            SensorHelper::updateSensorPost( $object );
                        }
                        catch( Exception $e )
                        {
                            eZDebug::writeError( $e->getMessage(), __METHOD__ );
                        }
                    }
                }
                elseif ( $object->attribute( 'class_identifier' ) == 'sensor_root'  )
                {
                    eZCache::clearByTag( 'template' );
                }
            }
        }
        elseif ( $trigger == 'pre_delete' )
        {
            $nodeIdList = $parameters['node_id_list'];
            $inTrash = (bool) $parameters['move_to_trash'];
            foreach( $nodeIdList as $nodeId )
            {
                $object = eZContentObject::fetchByNodeID( $nodeId );
                if ( $object instanceof eZContentObject && $object->attribute( 'class_identifier' ) == 'sensor_post' )
                {
                    try
                    {
                        SensorHelper::removeSensorPost( $object, $inTrash );
                    }
                    catch( Exception $e )
                    {
                        eZDebug::writeError( $e->getMessage(), __METHOD__ );
                    }
                }
            }
        }
    }

    protected static function needModeration( $timestamp = null, SensorUserInfo $userInfo = null )
    {
        if ( !$userInfo instanceof SensorUserInfo )
        {
            $userInfo = SensorUserInfo::current();
        }
        if ( $userInfo->hasModerationMode() )
        {
            return true;
        }

        if ( self::ModerationIsEnabled() )
        {
            return true;
        }

        if ( self::TimedModerationIsEnabled() )
        {
            if ( !$timestamp )
            {
                $timestamp = time();
            }
            $current = DateTime::createFromFormat( 'U', $timestamp );
            $dataMap = self::rootNodeDataMap();
            if ( $dataMap['office_timetable']->attribute( 'data_type_string' ) == 'ocrecurrence' )
            {
                $officeTimeTable = $dataMap['office_timetable']->content();
                return !$officeTimeTable->contains( $current );
            }
        }
        return false;
    }

    public static function ModerationIsEnabled()
    {
        $dataMap = self::rootNodeDataMap();
        return isset( $dataMap['enable_moderation'] )
               && $dataMap['enable_moderation']->attribute( 'data_int' ) == 1
               && $dataMap['enable_moderation']->attribute( 'data_type_string' ) == 'ezboolean';
    }

    public static function TimedModerationIsEnabled()
    {
        $dataMap = self::rootNodeDataMap();
        return isset( $dataMap['office_timetable'] )
               && $dataMap['office_timetable']->attribute( 'has_content' )
               && $dataMap['office_timetable']->attribute( 'data_type_string' ) == 'ocrecurrence';
    }

    /**
     * @param bool $asObject
     *
     * @return array|eZContentObjectTreeNode[]
     */
    protected static function fetchPosts( $asObject = false )
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
        $solrSearch = new OpenPASolr();
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

                $id = isset( $item['id_si'] ) ? $item['id_si'] : $item['id'];
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

    public static function rootHandler()
    {
        if ( !isset( $GLOBALS['SensorRootHandler'] ) )
        {
            $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
            $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
            $GLOBALS['SensorRootHandler'] = $rootHandler->attribute( 'control_sensor' );
        }
        return $GLOBALS['SensorRootHandler'];
    }

    public function defaultModerationStateIdentifier( SensorUserInfo $userInfo = null  )
    {
        return self::needModeration( null, $userInfo ) ? 'waiting' : null;
    }

    /**
     * Restituisce l'owner_id dell'oggetto corrente
     * @return int|null
     */
    public function getPostAuthorId()
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
    public function getApproverIdArray()
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
                    /** @var eZContentObjectAttribute[] $areaDataMap */
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
            $areas = self::areas();
            $area = isset( $areas['tree'][0]['node'] ) ? $areas['tree'][0]['node'] : false;
            if ( $area instanceof eZContentObjectTreeNode )
            {
                /** @var eZContentObjectAttribute[] $areaDataMap */
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
    public function getPostGeoJsArray()
    {
        $data = false;
        if ( $this->container->hasAttribute( 'geo' )
             &&  $this->container->attribute( 'geo' )->attribute( 'has_content' ) )
        {
            /** @var eZGmapLocation $content */
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
    public function getType()
    {
        $data = false;
        if ( $this->container->hasAttribute( 'type' )
             &&  $this->container->attribute( 'type' )->attribute( 'has_content' ) )
        {
            $content = $this->container->attribute( 'type' )->attribute(
                'contentobject_attribute'
            )->toString();
            if ( $content == 'suggerimento' )
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
            else // $content == 'segnalazione'
            {
                $data = array(
                    'name' => ezpI18n::tr( 'openpa_sensor/type', 'Segnalazione' ),
                    'identifier' => 'segnalazione',
                    'css_class' => 'info'
                );
            }
        }
        return $data;
    }

    public function getPostAuthorName()
    {
        $name = '?';
        if ( $this->container->getContentObject() instanceof eZContentObject )
        {
            /** @var eZContentObject $owner */
            $owner = $this->container->getContentObject()->attribute( 'owner' );
            if ( $owner )
            {
                $name = $owner->attribute( 'name' );
                if ( $this->container->hasAttribute( 'on_behalf_of' )
                     && $this->container->attribute( 'on_behalf_of' ) instanceof OpenPAAttributeHandler
                     && $this->container->attribute( 'on_behalf_of' )->attribute( 'has_content' ) )
                {
                    $name .= ' (' . $this->container->attribute( 'on_behalf_of' )->attribute( 'contentobject_attribute' )->toString() . ')';
                }
            }
        }
        return $name;
    }

    public function getPostCategoryName()
    {
        $name = '';
        if ( $this->container->hasAttribute( 'category' )
             && $this->container->attribute( 'category' ) instanceof OpenPAAttributeHandler
             && $this->container->attribute( 'category' )->attribute( 'has_content' ) )
        {
            $names = array();
            $categoryIds = explode( '-', $this->container->attribute( 'category' )->attribute( 'contentobject_attribute' )->toString() );
            foreach( $categoryIds as $categoryId )
            {
                $category = eZContentObject::fetch( $categoryId );
                if ( $category instanceof eZContentObject )
                {
                    $names[] = $category->attribute( 'name' );
                }
            }
            $name = implode( ' - ', $names );
        }
        return $name;
    }

    /**
     * Restituisce un array con nome identificatore e classcss del content object state di gruppo Moderation
     * @return array
     * @throws Exception
     */
    public function getCurrentModerationState()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
        {
            $states = OpenPABase::initStateGroup(
                self::$moderationStateGroupIdentifier,
                self::$moderationStateIdentifiers
            );
            foreach ( $states as $state )
            {
                if ( in_array( $state->attribute( 'id' ), $this->container->getContentObject()->attribute( 'state_id_array' ) ) )
                {
                    return array(
                        'name' => $state->attribute( 'current_translation' )->attribute( 'name' ),
                        'identifier' => $state->attribute( 'identifier' ),
                        'css_class' => 'danger'
                    );
                }
            }
        }
        return array();
    }

    /**
     * Restituisce un array con nome identificatore e classcss del content object state di gruppo Privacy
     * @return array
     * @throws Exception
     */
    public function getCurrentPrivacyState()
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
     * Restituisce un array con nome identificatore e classcss del content object state di gruppo Sensor
     * @return array
     * @throws Exception
     */
    public function getCurrentState()
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
    public static function areas()
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
    public static function categories()
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

    public function makePrivate()
    {
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            OpenPABase::sudo(
                function () use ( $object )
                {
                    ObjectHandlerServiceControlSensor::setState( $object, 'privacy', 'private' );
                }
            );
        }
    }

    public function makePublic()
    {
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            OpenPABase::sudo(
                function () use ( $object )
                {
                    ObjectHandlerServiceControlSensor::setState( $object, 'privacy', 'public' );
                }
            );
        }
    }

    public function moderate( $identifier )
    {
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            OpenPABase::sudo(
                function() use( $object, $identifier ){
                    ObjectHandlerServiceControlSensor::setState( $object, 'moderation', $identifier );
                }
            );
        }
    }

    public function setObjectState( $object, $status )
    {
        if ( $object instanceof eZContentObject )
        {
            if ( $status == SensorPost::STATUS_READ )
            {
                OpenPABase::sudo(
                    function () use ( $object )
                    {
                        ObjectHandlerServiceControlSensor::setState( $object, 'sensor', 'open' );
                    }
                );
            }
            elseif ( $status == SensorPost::STATUS_CLOSED )
            {
                OpenPABase::sudo(
                    function () use ( $object )
                    {
                        ObjectHandlerServiceControlSensor::setState( $object, 'sensor', 'close' );
                    }
                );
            }
            elseif ( $status == SensorPost::STATUS_REOPENED )
            {
                OpenPABase::sudo(
                    function () use ( $object )
                    {
                        ObjectHandlerServiceControlSensor::setState( $object, 'sensor', 'pending' );
                    }
                );
            }
        }
    }

    public function getContentObject()
    {
        return $this->container->getContentObject();
    }

    public function getContentObjectAttribute( $identifier )
    {
        $object = $this->getContentObject();
        if ( $object instanceof eZContentObject )
        {
            $dataMap = $object->attribute( 'data_map' );
            if ( isset( $dataMap[$identifier] ) )
            {
                return $dataMap[$identifier];
            }
        }
        return false;
    }

    public function setContentObjectAttribute( $identifier, $stringValue )
    {
        $attribute = $this->getContentObjectAttribute( $identifier );
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            $attribute->fromString( $stringValue );
            $attribute->store();
            eZContentCacheManager::clearContentCacheIfNeeded( $this->getContentObject()->attribute( 'id' ) );
            eZSearch::addObject( $this->getContentObject(), true );
            return true;
        }
        return false;
    }

    public function getApproverIdsByCategory()
    {
        $userIds = array();
        $category = $this->getContentObjectAttribute( 'category' );
        if ( $category instanceof eZContentObjectAttribute )
        {
            $categories = explode( '-', $category->toString() );
            foreach( $categories as $categoryId )
            {
                $category = eZContentObject::fetch( $categoryId );
                if ( $category instanceof eZContentObject )
                {
                    /** @var eZContentObjectAttribute[] $categoryDataMap */
                    $categoryDataMap = $category->attribute( 'data_map' );
                    if ( isset( $categoryDataMap['approver'] ) )
                    {
                        $userIds = array_merge( $userIds, explode( '-', $categoryDataMap['approver']->toString() ) );
                    }
                }
            }
        }
        return $userIds;
    }

    public function getPostUrl()
    {
        $url = 'http://' . $this->siteUrl() . '/sensor/posts/' . $this->getContentObject()->attribute( 'id' );
        $bitly = $this->bitlyShorten( $url );
        if ( isset( $bitly['url'] ) )
        {
            return $bitly['url'];
        }
        return $url;
    }

    protected function bitlyShorten( $longUrl, $accessToken = '6479f97a71b5793cd933c05723ce54dc5e29596a', $domain = '', $xLogin = 'o_29fu2l6mtt', $xApiKey = 'R_747525e9579e969d1f0e06f58f3896a2' )
    {
        $result = array();
        $url = "https://api-ssl.bit.ly/v3/shorten?access_token=" . $accessToken . "&longUrl=" . urlencode( $longUrl );
        if ( $domain != '' )
        {
            $url .= "&domain=" . $domain;
        }
        if ( $xLogin != '' && $xApiKey != '' )
        {
            $url .= "&x_login=" . $xLogin . "&x_apiKey=" . $xApiKey;
        }

        $output = "";
        try
        {
            $ch = curl_init( $url );
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $output = curl_exec($ch);
        }
        catch (Exception $e)
        {
        }
        $output = json_decode( $output );
        if ( isset( $output->{'data'}->{'hash'} ) )
        {
            $result['url'] = $output->{'data'}->{'url'};
            $result['hash'] = $output->{'data'}->{'hash'};
            $result['global_hash'] = $output->{'data'}->{'global_hash'};
            $result['long_url'] = $output->{'data'}->{'long_url'};
            $result['new_hash'] = $output->{'data'}->{'new_hash'};
        }
        $result['status_code'] = $output->status_code;
        return $result;
    }

    public static function createPost( SensorUserInfo $user, $data )
    {
        $params                     = array();
        $params['creator_id']       = $user->user()->id();
        $params['class_identifier'] = self::postContentClass()->attribute( 'identifier' );
        $params['parent_node_id']   = self::postContainerNode()->attribute( 'node_id' );
        $params['attributes']       = $data;
        return eZContentFunctions::createAndPublishObject( $params );
    }

    public static function updatePost( SensorUserInfo $user, $data, eZContentObject $contentObject )
    {
        /** @var eZContentObjectAttribute[] $contentObjectDataMap */
        $contentObjectDataMap = $contentObject->attribute( 'data_map' );
        $existingData = array();
        foreach( $contentObjectDataMap as $identifier => $attribute )
        {
            $existingData[$identifier] = $attribute->toString();
            if ( $attribute->hasContent() && !isset( $data[$identifier] ) )
            {
                $data[$identifier] = $attribute->toString();
            }
        }
        $params = array();
        $params['attributes'] = $data;
        eZContentFunctions::updateAndPublishObject( $contentObject, $params );
        return $contentObject;
    }

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

    public function sensorPostObjectFactory( SensorUserInfo $user, $data, eZContentObject $update = null )
    {
        if ( $update instanceof eZContentObject )
        {
            $id = $update->attribute( 'id' );
            eZContentObject::clearCache( array( $id ) );
            $update = eZContentObject::fetch( $id );
            return self::updatePost( $user, $data, $update );
        }
        else
        {
            return self::createPost( $user, $data );
        }
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

    public function getWhatsAppUserId()
    {
        $postContainerNode = self::postContainerNode();
        return $postContainerNode->attribute( 'contentobject_id' );
    }

    /**
     * @param $identifier
     *
     * @return bool
     */
    public static function rootNodeHasAttribute( $identifier )
    {
        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
        return $rootHandler->hasAttribute( $identifier );
    }

    public function siteTitle()
    {
        return strip_tags( $this->logoTitle() );
    }

    public function siteUrl()
    {
        $currentSiteaccess = eZSiteAccess::current();
        $sitaccessIdentifier = $currentSiteaccess['name'];
        if ( !self::isSensorSiteAccessName( $sitaccessIdentifier ) )
        {
            $sitaccessIdentifier = self::getSensorSiteAccessName();
        }
        $path = "settings/siteaccess/{$sitaccessIdentifier}/";
        $ini = new eZINI( 'site.ini.append', $path, null, null, null, true, true );
        return rtrim( $ini->variable( 'SiteSettings', 'SiteURL' ), '/' );
    }

    public function assetUrl()
    {
        $siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $siteUrl );
        if ( count( $parts ) >= 2 )
        {
            array_pop( $parts );
            $siteUrl = implode( '/', $parts );
        }
        return rtrim( $siteUrl, '/' );
    }

    public function logoPath()
    {
        $data = false;
        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
        if ( $rootHandler->hasAttribute( 'logo' ) )
        {
            $attribute = $rootHandler->attribute( 'logo' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute && $attribute->hasContent() )
            {
                /** @var eZImageAliasHandler $content */
                $content = $attribute->content();
                $original = $content->attribute( 'original' );
                $data = $original['full_path'];
            }
            else
            {
                $data = '/extension/openpa_sensor/design/standard/images/logo_sensor.png';
            }
        }
        return $data;
    }

    public function logoTitle()
    {
        return $this->getAttributeString( 'logo_title' );
    }

    public function logoSubtitle()
    {
        return $this->getAttributeString( 'logo_subtitle' );
    }

    public function headImages()
    {
        return array(
            "apple-touch-icon-114x114-precomposed" => null,
            "apple-touch-icon-72x72-precomposed" => null,
            "apple-touch-icon-57x57-precomposed" => null,
            "favicon" => null
        );
    }

    public function needLogin()
    {
        // TODO: Implement needLogin() method.
    }

    public function attributeContacts()
    {
        $data = '';
        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
        if ( $rootHandler->hasAttribute( 'contacts' ) )
        {
            $attribute = $rootHandler->attribute( 'contacts' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute )
            {
                $data = $attribute;
            }
        }
        return $data;
    }

    public function attributeFooter()
    {
        $data = '';
        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
        if ( $rootHandler->hasAttribute( 'footer' ) )
        {
            $attribute = $rootHandler->attribute( 'footer' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute )
            {
                $data = $attribute;
            }
        }
        return $data;
    }

    public function textCredits()
    {
        return ezpI18n::tr( 'sensor', 'Sensorcivico - progetto di riuso del Consorzio dei Comuni Trentini - realizzato da Opencontent con ComunWeb' );
    }

    public function googleAnalyticsId()
    {
        return OpenPAINI::variable( 'Seo', 'GoogleAnalyticsAccountID', false );
    }

    public function cookieLawUrl()
    {
        $href = 'sensor/info/cookie';
        eZURI::transformURI( $href, false, 'full' );
        return $href;
    }

    public function menu()
    {
        $menu = array(
            array(
                'name' => ezpI18n::tr( 'sensor/menu', 'Informazioni' ),
                'url' => 'sensor/info',
                'highlight' => false,
                'has_children' => true,
                'children' => array(
                    array(
                        'name' => ezpI18n::tr( 'sensor/menu', 'Faq' ),
                        'url' => 'sensor/info/faq',
                        'has_children' => false,
                    ),
                    array(
                        'name' => ezpI18n::tr( 'sensor/menu', 'Privacy' ),
                        'url' => 'sensor/info/privacy',
                        'has_children' => false,
                    ),
                    array(
                        'name' => ezpI18n::tr( 'sensor/menu', 'Termini di utilizzo' ),
                        'url' => 'sensor/info/terms',
                        'has_children' => false,
                    )
                )
            ),
            array(
                'name' => ezpI18n::tr( 'sensor/menu', 'Segnalazioni' ),
                'url' => 'sensor/posts',
                'highlight' => false,
                'has_children' => false
            )
        );
        if ( eZUser::currentUser()->isLoggedIn() )
        {
            $menu[] = array(
                'name' => ezpI18n::tr( 'sensor/menu', 'Le mie attività' ),
                'url' => 'sensor/dashboard',
                'highlight' => false,
                'has_children' => false
            );
            $menu[] = array(
                'name' => ezpI18n::tr( 'sensor/menu', 'Segnala' ),
                'url' => 'sensor/add',
                'highlight' => true,
                'has_children' => false
            );
        }
        return $menu;
    }

    public function bannerPath()
    {
        $data = false;
        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
        if ( $rootHandler->hasAttribute( 'banner' ) )
        {
            $attribute = $rootHandler->attribute( 'banner' )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute && $attribute->hasContent() )
            {
                /** @var eZImageAliasHandler $content */
                $content = $attribute->content();
                $original = $content->attribute( 'original' );
                $data = $original['full_path'];
            }
        }
        return $data;
    }

    public function bannerTitle()
    {
        return $this->getAttributeString( 'banner_title' );
    }

    public function bannerSubtitle()
    {
        return $this->getAttributeString( 'banner_subtitle' );
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getAttributeString( $identifier )
    {
        $data = '';
        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        $rootHandler = OpenPAObjectHandler::instanceFromContentObject( $root );
        if ( $rootHandler->hasAttribute( $identifier ) )
        {
            $attribute = $rootHandler->attribute( $identifier )->attribute( 'contentobject_attribute' );
            if ( $attribute instanceof eZContentObjectAttribute )
            {
                $data = self::replaceBracket( $attribute->toString() );
            }
        }
        return $data;
    }

    /**
     * Replace [ ] with strong html tag
     * @param string $string
     * @return string
     */
    public static function replaceBracket( $string )
    {
        $string = str_replace( '[', '<strong>', $string );
        $string = str_replace( ']', '</strong>', $string );
        return $string;
    }
}
