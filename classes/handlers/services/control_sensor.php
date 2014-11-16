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
        $this->fnData['collaboration_item'] = 'getCollaborationItem';

        $this->fnData['author_id'] = 'getAuthorId';
        $this->fnData['approver_id_array'] = 'getApproverIdArray';

        $this->fnData['geo_js_array'] = 'getGeoJsArray';

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

    protected function getCollaborationItem()
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
        return array(
            'name' => 'Segnalazione',
            'identifier' => 'segnalazione',
            'css_class' => 'info'
        );
    }

    protected function getCommentCount()
    {
        return 10;
    }

    protected function getCurrentOwner()
    {
        return 'Pinco Pallino - Ufficio test';
    }


    protected function getCurrentPrivacyStatus()
    {
        return array(
            'name' => 'Privato',
            'identifier' => 'private',
            'css_class' => 'default'
        );
    }

    protected function getCurrentStatus()
    {
        return array(
            'name' => 'In corso',
            'identifier' => 'open',
            'css_class' => 'danger'
        );
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
}