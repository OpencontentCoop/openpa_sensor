<?php

class ObjectHandlerServiceControlSensor extends ObjectHandlerServiceBase
{
    const USE_PER_AREA_APPROVER = false;

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
        $this->fnData['helper'] = 'getHelper';

        $this->fnData['author_id'] = 'getAuthorId';
        $this->fnData['approver_id_array'] = 'getApproverIdArray';

        $this->fnData['geo_js_array'] = 'getGeoJsArray';

        $this->fnData['type'] = 'getType';
        $this->fnData['current_status'] = 'getCurrentStatus';
        $this->fnData['current_privacy_status'] = 'getCurrentPrivacyStatus';
        $this->fnData['current_owner'] = 'getCurrentOwner';
        $this->fnData['comment_count'] = 'getCommentCount';
        $this->fnData['response_count'] = 'getResponseCount';

        $this->data['post_container_node'] = self::postContainerNode();
        $this->data['post_categories_container_node'] = self::postCategoriesNode();
        $this->data['post_class'] = self::postContentClass();
               
        $this->data['areas'] = self::postAreas();
        $this->data['categories'] = self::postCategories();
        $this->data['operators'] = self::operators();

        // forum
        $this->data['forum_container_node'] = self::forumContainerNode();
    }

    public static function PostIsEnable()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return isset( $dataMap['post_enabled'] ) && $dataMap['post_enabled']->attribute( 'data_int' ) == 1;
    }

    public static function ForumIsEnable()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return isset( $dataMap['forum_enabled'] ) && $dataMap['forum_enabled']->attribute( 'data_int' ) == 1;
    }

    public static function SurveyIsEnabled()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return isset( $dataMap['survey_enabled'] ) && $dataMap['survey_enabled']->attribute( 'data_int' ) == 1;
    }

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
    
    protected function getPrivacy()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return $dataMap['privacy'];
    }

    protected function getFaq()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return $dataMap['faq'];
    }

    protected function getTerms()
    {
        $node = self::rootNode();
        $dataMap = $node->attribute( 'data_map' );
        return $dataMap['terms'];
    }

    protected function getHelper()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
            return SensorHelper::instanceFromContentObjectId( $this->container->getContentObject()->attribute( 'id' ) );
        return null;
    }

    protected function getAuthorId()
    {
        if ( $this->container->getContentObject() instanceof eZContentObject )
            return $this->container->getContentObject()->attribute( 'owner_id' );
        return null;
    }

    protected function getApproverIdArray()
    {
        $data = array();
        if ( self::USE_PER_AREA_APPROVER )
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

    protected function getCommentCount()
    {
        return eZCollaborationItemMessageLink::fetchItemCount(
            array(
                'item_id' => $this->getHelper()->attribute( 'collaboration_item' )->attribute( 'id' ),
                'conditions' => array(
                    'message_type' => SensorHelper::MESSAGE_TYPE_PUBLIC
                )
            )
        );
    }
    
    protected function getResponseCount()
    {
        return eZCollaborationItemMessageLink::fetchItemCount(
            array(
                'item_id' => $this->getHelper()->attribute( 'collaboration_item' )->attribute( 'id' ),
                'conditions' => array(
                    'message_type' => SensorHelper::MESSAGE_TYPE_RESPONSE
                )
            )
        );
    }

    protected function getCurrentOwner()
    {
        $objectId = $this->getHelper()->attribute( 'owner_id' );
        if ( $objectId !== null )
        {
            $object = eZContentObject::fetch( $objectId );
            if ( $object instanceof eZContentObject )
            {
                $tpl = eZTemplate::factory();                            
                $tpl->setVariable( 'sensor_person', $object );
                return $tpl->fetch( 'design:content/view/sensor_person.tpl' );
            }
        }
        return false;

    }

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

    protected function getBannerTitle()
    {
        return $this->getAttributeString( 'banner_title' );
    }

    protected function getBannerSubTitle()
    {
        return $this->getAttributeString( 'banner_subtitle' );
    }

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

    protected function getSiteTitle()
    {
        return strip_tags( $this->getLogoTitle() );
    }

    protected function getLogoTitle()
    {
        return $this->getAttributeString( 'logo_title' );
    }

    protected function getLogoSubTitle()
    {
        return $this->getAttributeString( 'logo_subtitle' );
    }

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
     *
     * @throws Exception
     */
    public static function init( $options = array() )
    {
        OpenPASensorInstaller::run( $options );
    }

    public static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }

    public static function rootNode()
    {
        if ( self::$rootNode == null )
        {
            if ( !isset( $GLOBALS['SensorRootNode'] ) )
            {
                $GLOBALS['SensorRootNode'] = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() )->attribute( 'main_node' );    
            }            
            self::$rootNode = $GLOBALS['SensorRootNode'];
        }
        return self::$rootNode;
    }

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

    public static function postContentClass()
    {
        if ( self::$postContentClass == null )
        {
            self::$postContentClass = eZContentClass::fetchByIdentifier( 'sensor_post' );
        }
        return self::$postContentClass;
    }

    public static function forumCommentClass()
    {
        if ( self::$forumCommentClass == null )
        {
            self::$forumCommentClass = eZContentClass::fetchByIdentifier( 'dimmi_forum_reply' );
        }
        return self::$forumCommentClass;
    }
    
    public static  function operators()
    {
        return self::rootNode()->subTree( array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => array( 'user', 'sensor_operator', 'dipendente' ),
            'SortBy' => array( 'name', true )
        ) );
    }

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