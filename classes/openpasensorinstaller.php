<?php

class OpenPASensorInstaller implements OpenPAInstaller
{
    protected $options = array();

    protected $steps = array(
        '[0] segnalazioni',
        '[1] discussioni',
        '[2] consultazioni',
        '[3] ruoli',
        '[4] configurazioni ini',
    );

    protected $installOnlyStep;

    protected $environments = array();

    public function setScriptOptions( eZScript $script )
    {
        return $script->getOptions(
            '[parent-node:][step:][enable:][sa_suffix:][clean]',
            '',
            array(
                'parent-node' => 'Nodo id contenitore di sensor (Applicazioni di default)',
                'step' => 'Esegue solo lo step selezionato: gli step possibili sono' . implode( ', ', $this->steps ),
                'enable' => 'Abilita gli ambienti: [s] segnalazioni, [d] discussioni, [c] consultazioni (esempio: --enable=sd abilita le segnalazioni e le discussioni)',
                'sa_suffix' => 'Suffisso del siteaccess (default: partecipa)',
                'clean' => 'Elimina tutti i contenuti presenti di sensor prima di eseguire l\'installazione'
            )
        );
    }

    public function beforeInstall( $options = array() )
    {        
        eZContentClass::removeTemporary();
        $this->options = $options;

        if ( !isset( $this->options['sa_suffix'] ) )
        {
            $this->options['sa_suffix'] = 'partecipa';
        }

        if ( isset( $this->options['step'] ) )
        {
            if ( array_key_exists( $this->options['step'], $this->steps ) )
                $this->installOnlyStep = $this->options['step'];
            else
                throw new Exception( "Step {$this->options['step']} not found, run script with -h for help" );

            if ( isset( $this->options['clean'] ) )
            {
                throw new Exception( "Can not activate 'clean' with 'step' option" );
            }
        }

        if ( isset( $this->options['clean'] ) )
        {
            self::cleanup();
        }

        if ( isset( $this->options['enable'] ) )
        {
            $environments = str_split( $this->options['enable'] );
            foreach( $environments as $environment )
            {
                if ( $environment == 's' )
                {
                    $this->environments['post'] = true;
                }
                elseif ( $environment == 'd' )
                {
                    $this->environments['forum'] = true;
                }
                elseif ( $environment == 'c' )
                {
                    $this->environments['survey'] = true;
                }
                else
                {
                    throw new Exception( "Environment '{$environment}' does not exist, , run script with -h for help" );
                }
            }
        }
    }

    protected static function cleanup()
    {
        OpenPALog::warning( "Cleanup data" );
        $rootNode = ObjectHandlerServiceControlSensor::rootNode();
        if ( $rootNode instanceof eZContentObjectTreeNode )
        {
            eZContentObjectTreeNode::removeNode( $rootNode->attribute( 'node_id' ) );
        }
        unset( $GLOBALS['SensorRootNode'] );
        eZCollaborationItem::cleanup();
    }


    public function install()
    {
        OpenPALog::warning( "Controllo stati" );
        $states = self::installStates();

        OpenPALog::warning( "Controllo sezioni" );
        $section = self::installSections();

        OpenPALog::warning( "Controllo classi" );
        self::installClasses();

        OpenPALog::warning( "Installazione Sensor root" );
        if ( isset( $this->options['parent-node'] ) )
            $parentNodeId = $this->options['parent-node'];
        else
            $parentNodeId = OpenPAAppSectionHelper::instance()->rootNode()->attribute( 'node_id' );
        $root = self::installAppRoot( $parentNodeId, $section, $this->environments );

        if ( $this->installOnlyStep !== null )
        {
            OpenPALog::warning( "Install step " . $this->steps[$this->installOnlyStep] );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 0 ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( "Installazione Sensor segnalazioni" );
            self::installSensorPostStuff( $root, $section, $this->installOnlyStep === null );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 1 ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( "Installazione Sensor dimmi" );
            self::installSensorDimmiStuff( $root, $section, $this->installOnlyStep === null );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 2 ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( "Installazione Sensor consultazioni" );
            self::installSensorSurveyStuff( $root, $section, $this->installOnlyStep === null );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 3 ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( "Installazione ruoli" );
            self::installRoles( $section, $states );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 4 ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( 'Salvo configurazioni' );
            self::installIniParams( $this->options['sa_suffix'] );
        }

        eZCache::clearById( 'global_ini' );
        eZCache::clearById( 'template' );

        OpenPALog::error( "@todo Impostare i workflow di PostPublish e di PreDelete" );
    }

    public function afterInstall()
    {
        return false;
    }

    protected static function installSensorSurveyStuff( eZContentObject $rootObject, eZSection $section, $installDemoContent = true )
    {
        OpenPALog::setOutputLevel( OpenPALog::ERROR );
        $surveyInstaller = new OpenPASurveyInstaller();
        $surveyInstaller->install();
        OpenPALog::setOutputLevel( OpenPALog::ALL );

        $containerObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_survey' );
        if ( !$containerObject instanceof eZContentObject )
        {
            // Post container
            OpenPALog::warning( "Install Consultazioni container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_survey',
                'class_identifier' => 'consultation_root',
                'attributes' => array(
                    'name' => 'Consultazioni',
                    'short_description' => SQLIContentUtils::getRichContent( "<p>Uno strumento di consultazione per conoscere la tua opinione.</p>" ),
                    'description' => SQLIContentUtils::getRichContent( "<p>Uno strumento di consultazione per conoscere la tua opinione.</p>" ),
                    'image' => 'extension/openpa_sensor/doc/default/consultation_root.png'
                )
            );
            /** @var eZContentObject $containerObject */
            $containerObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$containerObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Consultazioni container' );
            }
        }

        if ( $containerObject->attribute( 'main_node' )->attribute( 'children_count' ) > 0 )
        {
            $installDemoContent = false;
        }

        if ( $installDemoContent )
        {
            OpenPALog::warning( "Install Survey demo " );
            $params = array(
                'parent_node_id' => $containerObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'consultation_survey',
                'attributes' => array(
                    'name' => 'Demo Consultazione',
                    'abstract' => SQLIContentUtils::getRichContent( "<p>Uno consultazione demo per conoscere la tua opinione.</p>" ),
                    'description' => SQLIContentUtils::getRichContent( "<p>Uno strumento di consultazione per conoscere la tua opinione.</p>" ),
                    'image' => 'extension/openpa_sensor/doc/default/consultation_root.png'
                )
            );
            /** @var eZContentObject $categoryObject */
            $surveyObject = eZContentFunctions::createAndPublishObject( $params );
            if ( !$surveyObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Survey demo' );
            }
        }
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
                    'title' => 'Quali sono le principali sfide che il tuo Comune sta affrontando?',
                    'subtitle' => 'Discutine qui con i tuoi concittadini',
                    'description' => SQLIContentUtils::getRichContent( "<p>Questo media civico istituzionale è aperto alla consultazione, confronto e partecipazione dei cittadini.</p><p>Per partecipare, basta effettuare una rapida registrazione.</p>" ),
                    'image' => 'extension/openpa_sensor/doc/default/dimmi_root.jpg'

                )
            );
            /** @var eZContentObject $containerObject */
            $containerObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$containerObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Dimmi container' );
            }
        }

        if ( $containerObject->attribute( 'main_node' )->attribute( 'children_count' ) > 0 )
        {
            $installDemoContent = false;
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
                    'title' => 'Demo'
                )
            );
            /** @var eZContentObject $categoryObject */
            $forumObject = eZContentFunctions::createAndPublishObject( $params );
            if ( !$forumObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Dimmi forum demo' );
            }

            for ( $i = 1; $i <= 2; $i++ )
            {
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
                if ( !$topicObject instanceof eZContentObject )
                {
                    throw new Exception( 'Failed creating Dimmi topic demo' );
                }
            }
        }
    }

    protected static function installStates()
    {
        $sensorStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$stateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$stateIdentifiers
        );

        $privacyStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$privacyStateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$privacyStateIdentifiers
        );
        
        $moderationStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$moderationStateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$moderationStateIdentifiers
        );
        return array_merge( $sensorStates, $privacyStates, $moderationStates );
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

    public static function sensorClassIdentifiers()
    {
        return array(
            "sensor_root",
            "sensor_area",
            "sensor_category",
            "sensor_operator",
            "sensor_post",
            "sensor_post_root",
            "dimmi_category",
            "dimmi_forum_reply",
            "dimmi_forum",
            "dimmi_root",
            "dimmi_forum_topic",
            "consultation_root",
            "consultation_survey"
        );
    }
    
    protected static function installClasses()
    {
        OpenPAClassTools::installClasses( OpenPASensorInstaller::sensorClassIdentifiers() );
    }

    protected static function installAppRoot( $parentNodeId, eZSection $section, $options = array() )
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
                    'name' => 'DimmiCittà',
                    'logo' => 'extension/openpa_sensor/doc/default/logo.png',
                    'logo_title' => 'Dimmi[Città]',
                    'logo_subtitle' => 'Confronto tra [cittadini] ed il Comune',
                    'banner' => 'extension/openpa_sensor/doc/default/banner.png',
                    'banner_title' => "Spazio istituzionale per il confronto con i cittadini e l'Amministrazione",
                    'banner_subtitle' => "L'ambiente adatto per discutere tra cittadini",
                    'faq' => SQLIContentUtils::getRichContent( "<p>Attraverso la<b>&nbsp;piattaforma SensorCivico</b>&nbsp;i/le cittadini/e possono formulare suggerimenti e problematiche rivolte a migliorare la vivibilità della tua Città.</p>" ),
                    'privacy' => SQLIContentUtils::getRichContent( "Da compilare" ),
                    'terms' => SQLIContentUtils::getRichContent( "Da compilare" ),
                    'footer' => SQLIContentUtils::getRichContent( "Da compilare" ),
                    'contacts' => SQLIContentUtils::getRichContent( "Da compilare" ),
                    'forum_enabled' => isset( $options['forum'] ),
                    'survey_enabled' => isset( $options['survey'] ),
                    'post_enabled' => isset( $options['post'] )
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
                'class_identifier' => 'sensor_post_root',
                'attributes' => array(
                    'name' => 'Segnala!',
                    'short_description' => SQLIContentUtils::getRichContent( "<p>Attraverso la piattaforma SensorCivico i cittadini possono formulare suggerimenti, segnalazioni e reclami su mappa per il miglioramento della qualità dei servizi offerti dall´Amministrazione e per migliorare la vivibilità della Città.</p>" ),
                    'description' => SQLIContentUtils::getRichContent( "<p>Attraverso la<b>&nbsp;piattaforma SensorCivico</b> i/le cittadini/e possono formulare suggerimenti, segnalazioni e reclami su mappa (OpenStreet map) per il miglioramento della qualità dei servizi offerti dall´Amministrazione e per migliorare la vivibilità della Città.<br>Tutti i suggerimenti, segnalazioni e reclami saranno resi pubblici a meno che il/la cittadino/a non abbiamo indicato diversamente in fase di caricamento della “segnalazione”.</p>" ),
                    'image' => 'extension/openpa_sensor/doc/default/sensor_post_root.png'
                )
            );
            /** @var eZContentObject $containerObject */
            $containerObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$containerObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor container node' );
            }
        }
        if ( $containerObject->attribute( 'class_identifier' ) == 'folder' )
        {
            $mapping = array(
                "name" => "name",
                "short_description" => "short_description",
                "description" => "description",
                "image" => ""
            );

            $conversionFunctions = new conversionFunctions();
            $containerObject = $conversionFunctions->convertObject( $containerObject->attribute('id'), eZContentClass::classIDByIdentifier( 'sensor_post_root' ), $mapping );
            if ( !$containerObject )
            {
                throw new Exception( "Errore nella conversione dell'oggetto contentitore" );
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

        if ( $groupObject->attribute( 'main_node' )->attribute( 'children_count' ) > 0 )
        {
            $installDemoContent = false;
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
                    'approver' => $operatorObject->attribute( 'id' ),
                    'geo' => '1|#46.0700915|#11.119762600000058|#'
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
                    'ModuleName' => 'survey',
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
                            eZContentClass::classIDByIdentifier( 'consultation_survey' )
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
                        'ParentClass' => eZContentClass::classIDByIdentifier( 'sensor_post_root' ),
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
                        'Section' => $section->attribute( 'id' )
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
                        'StateGroup_privacy' => $states['privacy.public']->attribute( 'id' ),
                        'StateGroup_moderation' => array(
                            $states['moderation.skipped']->attribute( 'id' ),
                            $states['moderation.accepted']->attribute( 'id' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_area' ),
                            eZContentClass::classIDByIdentifier( 'sensor_post_root' ),
                            eZContentClass::classIDByIdentifier( 'folder' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_category' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_reply' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum_topic' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_forum' ),
                            eZContentClass::classIDByIdentifier( 'dimmi_root' ),
                            eZContentClass::classIDByIdentifier( 'consultation_survey' ),
                            eZContentClass::classIDByIdentifier( 'consultation_root' )
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

    protected static function installIniParams( $saSuffix )
    {
        $sensor = OpenPABase::getCustomSiteaccessName( 'sensor', false );
        $sensorPath = "settings/siteaccess/{$sensor}/";

        // impostatzioni in backend
        $backend = OpenPABase::getBackendSiteaccessName();
        $backendPath = "settings/siteaccess/{$backend}/";
        $iniFile = "contentstructuremenu.ini";
        $ini = new eZINI( $iniFile . '.append', $backendPath, null, null, null, true, true );
        $value = array_unique( array_merge( (array) $ini->variable( 'TreeMenu', 'ShowClasses' ), array( 'sensor_root', 'dimmi_root', 'sensor_post_root', 'consultation_root' ) ) );
        $ini->setVariable( 'TreeMenu', 'ShowClasses', $value );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$backendPath}{$iniFile}" );

        $iniFile = "site.ini";
        $ini = new eZINI( $iniFile . '.append', $backendPath, null, null, null, true, true );
        $value = array_unique( array_merge( (array) $ini->variable( 'ExtensionSettings', 'ActiveAccessExtensions' ), array( 'openpa_sensor' ) ) );
        $ini->setVariable( 'ExtensionSettings', 'ActiveAccessExtensions', $value );
        $value = array_unique( array_merge( (array) $ini->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' ), array( $sensor ) ) );
        $ini->setVariable( 'SiteAccessSettings', 'RelatedSiteAccessList', $value );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$backendPath}{$iniFile}" );

        // impostatzioni in sensor
        eZDir::mkdir( $sensorPath );

        $frontend = OpenPABase::getFrontendSiteaccessName();
        $frontendPath = "settings/siteaccess/{$frontend}/";
        $frontendSiteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );

        eZFileHandler::copy( $frontendPath . 'site.ini.append.php', $sensorPath . 'site.ini.append.php' );
        $iniFile = "site.ini";
        $ini = new eZINI( $iniFile . '.append', $sensorPath, null, null, null, true, true );
        $ini->setVariable( 'ExtensionSettings', 'ActiveAccessExtensions', array( '', 'openpa_theme_2014', 'ocbootstrap', 'ocoperatorscollection', 'openpa_sensor' ) );
        $ini->setVariable( 'SiteSettings', 'SiteURL', $frontendSiteUrl . '/' . $saSuffix );
        $ini->setVariable( 'SiteSettings', 'DefaultPage', 'sensor/home' );
        $ini->setVariable( 'SiteSettings', 'IndexPage', 'sensor/home' );
        $ini->setVariable( 'SiteSettings', 'LoginPage', 'embedded' );
        $ini->setVariable( 'DesignSettings', 'SiteDesign', 'sensor' );
        $ini->setVariable( 'DesignSettings', 'AdditionalSiteDesignList', array( '', 'ocbootstrap', 'standard' ) );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$sensorPath}{$iniFile}" );

        $iniFile = "ezcomments.ini";
        $ini = new eZINI( $iniFile . '.append', $sensorPath, null, null, null, true, true );
        $ini->setVariable( 'RecaptchaSetting', 'PublicKey', '6Lee6v4SAAAAAKaBcnKYaMiD' );
        $ini->setVariable( 'RecaptchaSetting', 'PrivateKey', '6Lee6v4SAAAAAD39ImIzsTrIOkyPy2La13T7aZzf' );
        $ini->setVariable( 'RecaptchaSetting', 'Theme', 'custom' );
        $ini->setVariable( 'RecaptchaSetting', 'Language', 'en' );
        $ini->setVariable( 'RecaptchaSetting', 'TabIndex', '0' );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$sensorPath}{$iniFile}" );

        OpenPALog::error( "@todo Aggiungere siteaccess in override/site.ini:
[SiteSettings]SiteList[]={$sensor}
[SiteAccessSettings]AvailableSiteAccessList[]={$sensor}
[SiteAccessSettings]HostUriMatchMapItems[]={$frontendSiteUrl};{$saSuffix};{$sensor} \n" );
    }
}
