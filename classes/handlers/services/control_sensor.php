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
    protected static $postContainerNode;
    protected static $postContentClass;
    protected static $postAreas;

    protected static $stateGroupIdentifier = 'sensor';
    protected static $privacyStateGroupIdentifier = 'privacy';

    protected static $stateIdentifiers = array(
        'open' => "In corso",
        'close' => "Chiusa"
    );
    protected static $privacyStateIdentifiers = array(
        'public' => "Pubblico",
        'private' => "Privato",
    );

    function run()
    {
        $this->fnData['helper'] = 'getHelper';

        $this->fnData['author_id'] = 'getAuthorId';
        $this->fnData['approver_id_array'] = 'getApproverIdArray';

        $this->fnData['geo_js_array'] = 'getGeoJsArray';

        $this->fnData['operators'] = 'getOperators';

        $this->fnData['type'] = 'getType';
        $this->fnData['current_status'] = 'getCurrentStatus';
        $this->fnData['current_privacy_status'] = 'getCurrentPrivacyStatus';
        $this->fnData['current_owner'] = 'getCurrentOwner';
        $this->fnData['comment_count'] = 'getCommentCount';

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

        $this->fnData['footer_contacts'] = 'getFooterContacts';
        $this->fnData['footer_privacy'] = 'getFooterPrivacy';

        $this->data['post_container_node'] = self::postContainerNode();
        $this->data['post_class'] = self::postContentClass();

        $this->data['areas'] = self::postAreas();
    }

    protected function getOperators()
    {
        return self::rootNode()->subTree( array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => array( 'user' ),
            'SortBy' => array( 'name', true )
        ) );
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
                    'name' => ezpI18n::tr( 'openpa_sensor', 'Segnalazione' ),
                    'identifier' => 'segnalazione',
                    'css_class' => 'info'
                );
            }
            elseif ( $content == 'suggerimento' )
            {
                $data = array(
                    'name' => ezpI18n::tr( 'openpa_sensor', 'Suggerimento' ),
                    'identifier' => 'suggerimento',
                    'css_class' => 'warning'
                );
            }
            elseif ( $content == 'reclamo' )
            {
                $data = array(
                    'name' => ezpI18n::tr( 'openpa_sensor', 'Reclamo' ),
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

    protected function getCurrentOwner()
    {
        $object = eZContentObject::fetch( $this->getHelper()->attribute( 'owner_id' ) );
        if ( $object instanceof eZContentObject )
        {
            return $object->attribute( 'name' );
        }
        return '?';

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
                if ( in_array( $state->attribute( 'id' ), $this->container->getContentObject()->attribute( 'state_id_array' ) ) )
                {
                    return array(
                        'name' => $state->attribute( 'current_translation' )->attribute( 'name' ),
                        'identifier' => $state->attribute( 'identifier' ),
                        'css_class' => $state->attribute( 'identifier' ) == 'open' ? 'danger' : 'success'
                    );
                }
            }
        }
        return array();
    }

    protected function getFooterPrivacy()
    {
        return $this->getAttributeString( 'footer_contacts' );
    }

    protected function getFooterContacts()
    {
        return $this->getAttributeString( 'footer_privacy' );
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
                $data = $this->replaceBracket( $attribute->toString() );
            }
        }
        return $data;
    }


    protected function replaceBracket( $string )
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
        OpenPABase::initStateGroup(
            self::$stateGroupIdentifier,
            self::$stateIdentifiers
        );

        OpenPABase::initStateGroup(
            self::$privacyStateGroupIdentifier,
            self::$privacyStateIdentifiers
        );

        $section = OpenPABase::initSection(
            self::SECTION_NAME,
            self::SECTION_IDENTIFIER,
            OpenPAAppSectionHelper::NAVIGATION_IDENTIFIER
        );

        $classes = array(
            "sensor_root",
            "sensor_area",
            "sensor_operator",
            "sensor_post"
        );

        foreach( $classes as $identifier )
        {
            //OpenPALog::warning( 'Controllo class ' . $identifier );
            $tools = new OpenPAClassTools( $identifier, true );
            if ( !$tools->isValid() )
            {
                $tools->sync( true );
                OpenPALog::warning( "La classe $identifier è stata aggiornata" );
            }
        }

        $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );
        if ( !$root instanceof eZContentObject )
        {
            if ( isset( $options['parent-node'] ) )
            {
                $parentNodeId = $options['parent-node'];
            }
            else
            {
                $parentNodeId = OpenPAAppSectionHelper::instance()->rootNode()->attribute(
                    'node_id'
                );
            }

            $params = array(
                'parent_node_id' => $parentNodeId,
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => self::sensorRootRemoteId(),
                'class_identifier' => 'sensor_root',
                'attributes' => array(
                    'name' => 'Sensor'
                )
            );

            /** @var eZContentObject $contentObject */
            $contentObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$contentObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Apps root node' );
            }

            $params = array(
                'parent_node_id' => $contentObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => self::sensorRootRemoteId() . '_postcontainer',
                'class_identifier' => 'folder',
                'attributes' => array(
                    'name' => 'Posts'
                )
            );

            /** @var eZContentObject $contentObject */
            $contentObject = eZContentFunctions::createAndPublishObject( $params );

        }
    }
    public static function sensorRootRemoteId()
    {
        return OpenPABase::getCurrentSiteaccessIdentifier() . '_openpa_sensor';
    }


    public static function rootNode()
    {
        if ( self::$rootNode == null )
        {
            $root = eZContentObject::fetchByRemoteID( self::sensorRootRemoteId() );

            self::$rootNode = $root->attribute( 'main_node' );
        }
        return self::$rootNode;
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

    public function postAreas()
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
                    'SortBy' => array( 'depth', true )
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

    protected static function walkSubtree( eZContentObjectTreeNode $node, &$coords )
    {
        $data = array();
        if ( $node->childrenCount() > 0 )
        {
            foreach( $node->children() as $subNode )
            {
                self::findAreaCoords( $subNode->attribute( 'object' ), $coords );
                $data[] = array(
                    'node' => $subNode,
                    'children' => self::walkSubtree( $subNode, $coords )
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

    public static function executeWorkflow( $parameters, $process, $event )
    {
        $id = $parameters['object_id'];
        $object = eZContentObject::fetch( $id );
        if ( $object instanceof eZContentObject
             && $object->attribute( 'class_identifier' ) == 'sensor_post'
             && $object->attribute( 'current_version') == 1 )
        {
            SensorHelper::createCollaborationItem( $id );
        }

    }
}