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
    protected static $postCategoriesNode;
    protected static $postContentClass;
    protected static $postAreas;
    protected static $postCategories;

    protected static $stateGroupIdentifier = 'sensor';
    protected static $privacyStateGroupIdentifier = 'privacy';

    protected static $stateIdentifiers = array(
        'pending' => "Inviato",
        'open' => "In carico",
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

        $this->fnData['type'] = 'getType';
        $this->fnData['current_status'] = 'getCurrentStatus';
        $this->fnData['current_privacy_status'] = 'getCurrentPrivacyStatus';
        $this->fnData['current_owner'] = 'getCurrentOwner';
        $this->fnData['comment_count'] = 'getCommentCount';
        $this->fnData['response_count'] = 'getResponseCount';

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

        $this->data['post_container_node'] = self::postContainerNode();
        $this->data['post_categories_container_node'] = self::postCategoriesNode();
        $this->data['post_class'] = self::postContentClass();
               
        $this->data['areas'] = self::postAreas();
        $this->data['categories'] = self::postCategories();
        $this->data['operators'] = self::operators();
        
        $this->fnData['sensor_url'] = 'getSensorSiteaccessUrl';
        $this->fnData['sensor_asset_url'] = 'getAssetUrl';

    }

    protected function getAssetUrl()
    {
        $sitaccessIdentifier = OpenPABase::getFrontendSiteaccessName();
        $path = "settings/siteaccess/{$sitaccessIdentifier}/";
        $ini = new eZINI( 'site.ini.append', $path, null, null, null, true, true );        
        return rtrim( $ini->variable( 'SiteSettings', 'SiteURL' ), '/' );
    }
    
    protected function getSensorSiteaccessUrl()
    {
        $sitaccessIdentifier = OpenPABase::getCurrentSiteaccessIdentifier() . '_sensor';
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
        $object = eZContentObject::fetch( $this->getHelper()->attribute( 'owner_id' ) );
        if ( $object instanceof eZContentObject )
        {
            return $object->attribute( 'name' );
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
            "sensor_category",
            "sensor_operator",
            "sensor_post"
        );

        OpenPALog::warning( "Controllo classi" );
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

            // root
            OpenPALog::warning( "Install root" );
            $params = array(
                'parent_node_id' => $parentNodeId,
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => self::sensorRootRemoteId(),
                'class_identifier' => 'sensor_root',
                'attributes' => array(
                    'name' => 'SensorCivico'
                )
            );
            /** @var eZContentObject $rootObject */
            $rootObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$rootObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor root node' );
            }

            // Post container
            OpenPALog::warning( "Install container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => self::sensorRootRemoteId() . '_postcontainer',
                'class_identifier' => 'folder',
                'attributes' => array(
                    'name' => 'Segnalazioni'
                )
            );
            /** @var eZContentObject $containerObject */
            $containerObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$containerObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor container node' );
            }

            // Operator group
            OpenPALog::warning( "Install group" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => self::sensorRootRemoteId() . '_operators',
                'class_identifier' => 'user_group',
                'attributes' => array(
                    'name' => 'Operatori'
                )
            );
            /** @var eZContentObject $groupObject */
            $groupObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$groupObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor group node' );
            }

            // Operator sample
            $params = array(
                'parent_node_id' => $groupObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'sensor_operator',
                'attributes' => array(
                    'name' => 'Responsabile URP'
                )
            );
            /** @var eZContentObject $categoryObject */
            $operatorObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$operatorObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor operator node' );
            }

            // Area container
            OpenPALog::warning( "Install area" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'sensor_area',
                'attributes' => array(
                    'name' => eZINI::instance()->variable( 'SiteSettings', 'SiteName' ),
                    'approver' => $operatorObject->attribute( 'id' )
                )
            );
            /** @var eZContentObject $areaObject */
            $areaObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$areaObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor area node' );
            }

            // Categories container
            OpenPALog::warning( "Install category" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => self::sensorRootRemoteId() . '_postcategories',
                'class_identifier' => 'folder',
                'attributes' => array(
                    'name' => 'Categorie'
                )
            );
            /** @var eZContentObject $categoriesObject */
            $categoriesObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$categoriesObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor categories node' );
            }

            // Category sample
            $params = array(
                'parent_node_id' => $categoriesObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'sensor_category',
                'attributes' => array(
                    'name' => 'Esempio'
                )
            );
            /** @var eZContentObject $categoryObject */
            $categoryObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$categoryObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor category node' );
            }

            $roles = array(

                "Sensor Admin" => array(

                    array( 'ModuleName' => 'apps',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'openpa',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'sensor',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'user',
                           'FunctionName' => 'login' ),

                    array( 'ModuleName' => 'websitetoolbar',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'edit',
                           'Limitation' => array( 'Section' => $section->attribute( 'id' ) ) ),

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'read' ),

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'remove',
                           'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                                                  'Section' => $section->attribute( 'id' ) ) )
                ),

                "Sensor Operators" => array(

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'read',
                           'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'dipendente' ) ) ),
                    
                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'read',
                           'Limitation' => array( 'Class' => array( eZContentClass::classIDByIdentifier( 'sensor_area' ),
                                                                    eZContentClass::classIDByIdentifier( 'sensor_operator' ) ),
                                                  'Section' => $section->attribute( 'id' ) ) ),

                    array( 'ModuleName' => 'notification',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'sensor',
                           'FunctionName' => 'manage' ),

                ),

                "Sensor Reporter" => array(

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'create',
                           'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                                                  'ParentClass' => eZContentClass::classIDByIdentifier( 'folder' ),
                                                  'Section' => $section->attribute( 'id' ) ) ),

                    array( 'ModuleName' => 'notification',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'user',
                           'FunctionName' => 'login' ), //@todo siteaccess

                ),

                "Sensor Anonymous" => array(

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'read',
                           'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                                                  'Owner' => 1,
                                                  'Section' => $section->attribute( 'id' ),
                                                  'StateGroup_privacy' => ''/*@todo PRIVATO*/ )  ),

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'read',
                           'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                                                  'Section' => $section->attribute( 'id' ),
                                                  'StateGroup_privacy' => ''/*@todo PUBBLCO*/ )  ),

                    array( 'ModuleName' => 'content',
                           'FunctionName' => 'read',
                           'Limitation' => array( 'Class' => array( eZContentClass::classIDByIdentifier( 'sensor_area' ),
                                                                    eZContentClass::classIDByIdentifier( 'folder' ) ),
                                                  'Section' => $section->attribute( 'id' ) ) ),

                    array( 'ModuleName' => 'sensor',
                           'FunctionName' => 'use' ),

                    array( 'ModuleName' => 'collaboration',
                           'FunctionName' => '*' ),

                    array( 'ModuleName' => 'user',
                           'FunctionName' => 'login' ), //@todo siteaccess

                )
            );

            OpenPALog::warning( "Install roles" );
            foreach( $roles as $roleName => $policies )
            {
                OpenPABase::initRole( $roleName, $policies );
            }

            $anonymousUserId = eZINI::instance()->variable( 'UserSettings', 'AnonymousUserID' );
            /** @var eZRole $anonymousRole */
            $anonymousRole = eZRole::fetchByName( "Sensor Anonymous" );
            if ( !$anonymousRole instanceof eZRole )
            {
                throw new Exception( "Error: problem with roles" );
            }
            $anonymousRole->assignToUser( $anonymousUserId );

            /** @var eZRole $reporterRole */
            $reporterRole = eZRole::fetchByName( "Sensor Reporter" );
            if ( !$reporterRole instanceof eZRole )
            {
                throw new Exception( "Error: problem with roles" );
            }
            $memberNodeId = eZINI::instance()->variable( 'UserSettings', 'DefaultUserPlacement' );
            $members = eZContentObject::fetchByNodeID( $memberNodeId );
            if ( $members instanceof eZContentObject )
            {
                $anonymousRole->assignToUser( $members->attribute( 'id' ) );
                $reporterRole->assignToUser( $members->attribute( 'id' ) );
            }

            /** @var eZRole $operatorRole */
            $operatorRole = eZRole::fetchByName( "Sensor Operators" );
            if ( !$operatorRole instanceof eZRole )
            {
                throw new Exception( "Error: problem with roles" );
            }
            $anonymousRole->assignToUser( $groupObject->attribute( 'id' ) );
            $reporterRole->assignToUser( $groupObject->attribute( 'id' ) );
            $operatorRole->assignToUser( $groupObject->attribute( 'id' ) );

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
    
    public static function postCategories()
    {
        if ( self::$postCategories == null )
        {
            $data = array();
            $false = false;
            /** @var eZContentObjectTreeNode[] $treeAreas */
            $treeCategories = self::postCategoriesNode()->subTree( array(
                    'ClassFilterType' => 'include',
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'ClassFilterArray' => array( 'sensor_category' ),
                    'Limitation' => array(),
                    'SortBy' => array( 'depth', true )
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

    protected static function walkSubtree( eZContentObjectTreeNode $node, &$coords )
    {
        $data = array();
        if ( $node->childrenCount() > 0 )
        {
            foreach( $node->children() as $subNode )
            {
                if ( is_array( $coords ) )
                {
                    self::findAreaCoords( $subNode->attribute( 'object' ), $coords );
                }
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
        if ( $trigger == 'post_publish' )
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
                        self::setState( $object, 'privacy', 'private' );
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
        $solrResult = $solrSearch->search( '', $solrFetchParams );
        return $solrResult;
    }
    
    function fetchSensorGeoJsonFeatureCollection()
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