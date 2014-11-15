<?php

class ObjectHandlerServiceControlSensor extends ObjectHandlerServiceBase
{
    const SECTION_IDENTIFIER = "sensor";
    const SECTION_NAME = "Sensor";

    /**
     * @var eZContentObjectTreeNode
     */
    protected static $rootNode;
    protected static $postContainerNode;
    protected static $postContentClass;

    protected static $stateGroupIdentifier = 'sensor';

    protected static $stateIdentifiers = array(
        'open' => "In corso",
        'close' => "Chiusa"
    );

    function run()
    {
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
}