<?php

class OpenPASensorInstaller
{
    public static function run( $options = array() )
    {
        $states = self::installStates();

        $section = self::installSections();

        OpenPALog::warning( "Controllo classi" );
        self::installClasses();

        OpenPALog::warning( "Installazione Sensor root" );
        if ( isset( $options['parent-node'] ) )
            $parentNodeId = $options['parent-node'];
        else
            $parentNodeId = OpenPAAppSectionHelper::instance()->rootNode()->attribute( 'node_id' );
        $root = self::installAppRoot( $parentNodeId, $section );

        OpenPALog::warning( "Installazione Sensor segnalazioni" );
        self::installSensorPostStuff( $root, $section, true );

        OpenPALog::warning( "Installazione Sensor dimmi" );
        self::installSensorDimmiStuff( $root, $section, true );

        OpenPALog::warning( "Installazione ruoli" );
        self::installRoles( $section, $states );

        OpenPALog::warning( 'Salvo configurazioni' );
        self::installIniParams();

        eZCache::clearById( 'global_ini' );
        eZCache::clearById( 'template' );
        
        OpenPALog::error( "@todo Impostare i workflow di PostPublish e di PreDelete" );

    }

    protected static function installSensorDimmiStuff( eZContentObject $rootObject, eZSection $section, $installDemoContent = true )
    {
        $containerObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_dimmi' );
        if ( !$containerObject instanceof eZContentObject )
        {
            // Post container
            OpenPALog::warning( "Install Dimmi container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_dimmi',
                'class_identifier' => 'dimmi_root',
                'attributes' => array(
                    'title' => 'Discussioni'
                )
            );
            /** @var eZContentObject $containerObject */
            $containerObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$containerObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Dimmi container' );
            }
        }

        if ( $installDemoContent )
        {
            // Forum sample
            OpenPALog::warning( "Install Forum demo " );
            $params = array(
                'parent_node_id' => $containerObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'dimmi_forum',
                'attributes' => array(
                    'name' => 'Demo'
                )
            );
            /** @var eZContentObject $categoryObject */
            $forumObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$forumObject  instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Dimmi forum demo' );
            }

            // Topic sample
            OpenPALog::warning( "Install Topic demo " );
            $params = array(
                'parent_node_id' => $forumObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'dimmi_forum_topic',
                'attributes' => array(
                    'subject' => 'Demo'
                )
            );
            /** @var eZContentObject $categoryObject */
            $topicObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$topicObject   instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Dimmi topic demo' );
            }
        }
    }

    protected static function installStates()
    {
        OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$stateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$stateIdentifiers
        );

        $states = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$privacyStateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$privacyStateIdentifiers
        );
        return $states;
    }

    protected static function installSections()
    {
        $section = OpenPABase::initSection(
            ObjectHandlerServiceControlSensor::SECTION_NAME,
            ObjectHandlerServiceControlSensor::SECTION_IDENTIFIER,
            OpenPAAppSectionHelper::NAVIGATION_IDENTIFIER
        );
        return $section;
    }

    protected static function installClasses()
    {
        OpenPAClassTools::installClasses( array(
            "sensor_root",
            "sensor_area",
            "sensor_category",
            "sensor_operator",
            "sensor_post",
            "dimmi_category",
            "dimmi_forum_reply",
            "dimmi_forum",
            "dimmi_root",
            "dimmi_forum_topic",
        ) );
    }

    protected static function installAppRoot( $parentNodeId, eZSection $section )
    {
        $rootObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() );
        if ( !$rootObject instanceof eZContentObject )
        {
            // root
            $params = array(
                'parent_node_id' => $parentNodeId,
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId(),
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
        }
        return $rootObject;
    }

    protected static function installSensorPostStuff( eZContentObject $rootObject, eZSection $section, $installDemoContent = true )
    {
        $containerObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcontainer' );
        if ( !$containerObject instanceof eZContentObject )
        {
            // Post container
            OpenPALog::warning( "Install Post container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcontainer',
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
        }

        $groupObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_operators' );
        if ( !$groupObject instanceof eZContentObject )
        {
            // Operator group
            OpenPALog::warning( "Install Operators group" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_operators',
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
        }

        if ( $installDemoContent )
        {
            // Operator sample
            OpenPALog::warning( "Install Operator demo as main operator" );
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
            OpenPALog::warning( "Install Area demo as container" );
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
        }
        $categoriesObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcategories' );
        if ( !$categoriesObject instanceof eZContentObject )
        {
            // Categories container
            OpenPALog::warning( "Install Category container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcategories',
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
        }

        if ( $installDemoContent )
        {
            // Category sample
            OpenPALog::warning( "Install Category demo" );
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
        }
    }

    protected static function installRoles( eZSection $section, array $states )
    {
        $roles = array(

            "Sensor Admin" => array(
                array(
                    'ModuleName' => 'apps',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'openpa',
                    'FunctionName' => '*'
                ),

                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'user',
                    'FunctionName' => 'login',
                    'Limitation' => array(
                        'SiteAccess' => eZSys::ezcrc32( OpenPABase::getCustomSiteaccessName( 'sensor' ) )
                    )
                ),
                array(
                    'ModuleName' => 'websitetoolbar',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'edit',
                    'Limitation' => array( 'Section' => $section->attribute( 'id' ) )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array( 'Section' => $section->attribute( 'id' ) )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'remove',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_post' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_topic' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_reply' ),
                        ),
                        'Section' => $section->attribute( 'id' )
                    )
                )
            ),

            "Sensor Operators" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'dipendente' ) )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_area' ),
                            eZContentClass::classIDByIdentifier( 'sensor_operator' )
                        ),
                        'Section' => $section->attribute( 'id' ) )
                ),
                array(
                    'ModuleName' => 'notification',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => 'manage'
                ),
            ),

            "Sensor Reporter" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                        'ParentClass' => eZContentClass::classIDByIdentifier( 'folder' ),
                        'Section' => $section->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( 'dimmi_forum_reply' ),
                        'ParentClass' => array(
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_topic' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_reply' )
                        ),
                        'Section' => $section->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                        'Owner' => 1,
                        'Section' => $section->attribute( 'id' ),
                        'StateGroup_privacy' => $states['privacy.private']->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'notification',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'user',
                    'FunctionName' => 'login',
                    'Limitation' => array(
                        'SiteAccess' => eZSys::ezcrc32( OpenPABase::getCustomSiteaccessName( 'sensor' ) )
                    )
                ),
                array(
                    'ModuleName' => 'collaboration',
                    'FunctionName' => '*'
                ),
            ),
            
            "Sensor Assistant" => array(
                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => 'behalf'
                )
            ),

            "Sensor Anonymous" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                        'Section' => $section->attribute( 'id' ),
                        'StateGroup_privacy' => $states['privacy.public']->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_area' ),
                            eZContentClass::classIDByIdentifier( 'folder' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_category' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_reply' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_topic' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_root' ),
                        ),
                        'Section' => $section->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => 'use'
                ),
                array(
                    'ModuleName' => 'user',
                    'FunctionName' => 'login',
                    'Limitation' => array(
                        'SiteAccess' => eZSys::ezcrc32( OpenPABase::getCustomSiteaccessName( 'sensor' ) )
                    )
                ),
            )
        );

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

        $groupObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_operators' );
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

    protected static function installIniParams()
    {        
        $backend = OpenPABase::getBackendSiteaccessName();
        $path = "settings/siteaccess/{$backend}/";
        $iniFile = "contentstructuremenu.ini";
        $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );
        $value = array_unique( array_merge( (array) $ini->variable( 'TreeMenu', 'ShowClasses' ), array( 'sensor_root', 'dimmi_root' ) ) );
        $ini->setVariable( 'TreeMenu', 'ShowClasses', $value );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare contentstructuremenu.ini" );
        
        OpenPALog::error( "@todo Creare cartella di steaccess " . OpenPABase::getCustomSiteaccessName( 'sensor' ) );
        OpenPALog::error( "@todo Aggiungere siteaccess in override/site.ini" );
        OpenPALog::error( "@todo Aggiungere ActiveAccessExtensions[]=openpa_sensor in " . OpenPABase::getBackendSiteaccessName() . "/site.ini.append.php" );
        OpenPALog::error( "@todo Aggiungere RelatedSiteAccessList[]=" . OpenPABase::getCustomSiteaccessName( 'sensor' ) . " in " . OpenPABase::getBackendSiteaccessName() . "/site.ini.append.php" );
    }


}
