<?php

use Opencontent\Sensor\Legacy\Utils\TreeNode;

class ObjectHandlerServiceControlSensor extends ObjectHandlerServiceBase implements OCPageDataHandlerInterface
{
    private $repository;

    function __construct($data = array())
    {
        parent::__construct($data);
        $this->repository = OpenPaSensorRepository::instance();
    }

    function run()
    {
    }

    public static function isSensorSiteAccessName($currentSiteAccessName)
    {
        return OpenPABase::getCustomSiteaccessName('sensor') == $currentSiteAccessName;
    }

    public static function getSensorSiteAccessName()
    {
        return OpenPABase::getCustomSiteaccessName('sensor');
    }

    /**
     * @param array $parameters
     * @param eZWorkflowProcess $process
     * @param eZWorkflowEvent $event
     *
     * @throws Exception
     */
    public static function executeWorkflow($parameters, $process, $event)
    {
        $trigger = $parameters['trigger_name'];
        eZDebug::writeDebug("Sensor workflow for $trigger", __METHOD__);
        $repository = OpenPaSensorRepository::instance();

        if ($trigger == 'pre_publish') {
            $id = $parameters['object_id'];
            $object = eZContentObject::fetch($id);
            $version = $object->version($parameters['version']);
            if ($object instanceof eZContentObject) {

                // create/update sensor stuff
                if ($object->attribute('class_identifier') == 'sensor_post') {
                    $postInitializer = new \Opencontent\Sensor\Legacy\PostService\PostInitializer(
                        $repository,
                        $object,
                        $version
                    );
                    if ($parameters['version'] == 1) {
                        try {
                            $postInitializer->init();
                        } catch (Exception $e) {
                            $repository->getLogger()->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
                            eZDebug::writeError($e->getMessage(), __METHOD__);
                        }
                    } else {
                        try {
                            $postInitializer->refresh();
                        } catch (Exception $e) {
                            $repository->getLogger()->error($e->getMessage(), ['method' => __METHOD__, 'line' => __LINE__]);
                            eZDebug::writeError($e->getMessage(), __METHOD__);
                        }
                    }

                // empty cache
                } elseif ($object->attribute('class_identifier') == 'sensor_root') {
                    eZCache::clearByTag('template');

                // empty area tree cache
                } elseif ($object->attribute('class_identifier') == 'sensor_area') {
                    TreeNode::clearCache($repository->getAreasRootNode()->attribute('node_id'));

                // empty category tree cache
                } elseif ($object->attribute('class_identifier') == 'sensor_category') {
                    TreeNode::clearCache($repository->getCategoriesRootNode()->attribute('node_id'));

                } elseif ($object->attribute('class_identifier') == 'sensor_group') {
                    TreeNode::clearCache($repository->getOperatorsRootNode()->attribute('node_id'));
                    TreeNode::clearCache($repository->getGroupsRootNode()->attribute('node_id'));
                    exec("sh extension/openpa_sensor/bin/bash/reindex_by_class.sh sensor_operator");

                // set default dahboard filters
                } elseif ($object->attribute('class_identifier') == 'sensor_operator') {
                    if ($object->attribute('current_version') == 1) {
                        eZPreferences::setValue('sensor_participant_filter_approver', 1, $id);
                        eZPreferences::setValue('sensor_participant_filter_owner', 1, $id);
                    }
                    TreeNode::clearCache($repository->getOperatorsRootNode()->attribute('node_id'));
                    TreeNode::clearCache($repository->getGroupsRootNode()->attribute('node_id'));

                // set default notification subscriptions
                } elseif ($object->attribute('class_identifier') == 'user' && $object->attribute('current_version') == 1) {
                    $defaultNotificationRules = ['on_create', 'on_assign', 'on_close', 'reminder'];
                    $notificationPrefix = $repository->getSensorCollaborationHandlerTypeString() . '_';
                    foreach ($defaultNotificationRules as $rule) {
                        $defaultNotificationRule = $notificationPrefix . $rule;
                        eZCollaborationNotificationRule::create($defaultNotificationRule, $id)->store();
                    }
                }
            }

        } elseif ($trigger == 'pre_delete') {
            $nodeIdList = $parameters['node_id_list'];
            $inTrash = (bool)$parameters['move_to_trash'];
            foreach ($nodeIdList as $nodeId) {
                $object = eZContentObject::fetchByNodeID($nodeId);
                // remove sensor stuff
                if ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'sensor_post') {
                    try {
                        $postInitializer = new \Opencontent\Sensor\Legacy\PostService\PostInitializer(
                            $repository,
                            $object,
                            $object->currentVersion()
                        );
                        if ($inTrash) {
                            $postInitializer->trash();
                        } else {
                            $postInitializer->delete();
                        }
                    } catch (Exception $e) {
                        eZDebug::writeError($e->getMessage(), __METHOD__);
                    }

                // empty area tree cache
                } elseif ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'sensor_area') {
                    TreeNode::clearCache($repository->getAreasRootNode()->attribute('node_id'));

                // empty category tree cache
                } elseif ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'sensor_category') {
                    TreeNode::clearCache($repository->getCategoriesRootNode()->attribute('node_id'));

                } elseif ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'sensor_operator') {
                    TreeNode::clearCache($repository->getOperatorsRootNode()->attribute('node_id'));
                    TreeNode::clearCache($repository->getGroupsRootNode()->attribute('node_id'));

                } elseif ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'sensor_group') {
                    TreeNode::clearCache($repository->getOperatorsRootNode()->attribute('node_id'));
                    TreeNode::clearCache($repository->getGroupsRootNode()->attribute('node_id'));
                    exec("sh extension/openpa_sensor/bin/bash/reindex_by_class.sh sensor_operator");
                }
            }
        }
    }

    public function siteTitle()
    {
        return strip_tags($this->logoTitle());
    }

    public function siteUrl()
    {
        $currentSiteaccess = eZSiteAccess::current();
        $sitaccessIdentifier = $currentSiteaccess['name'];
        if (!self::isSensorSiteAccessName($sitaccessIdentifier)) {
            $sitaccessIdentifier = self::getSensorSiteAccessName();
        }
        $path = "settings/siteaccess/{$sitaccessIdentifier}/";

        $ini = new eZINI('site.ini', $path, null, null, null, true, true);
        return rtrim($ini->variable('SiteSettings', 'SiteURL'), '/');
    }

    public function assetUrl()
    {
        // se il cluster è su aws o simili l'asset url è già presente nell'url delle immagini
        if (eZINI::instance('file.ini')->variable('eZDFSClusteringSettings', 'DFSBackend') == 'OpenPADFSFileHandlerDFSDispatcher'){
            return '';
        }

        $siteUrl = eZINI::instance()->variable('SiteSettings', 'SiteURL');
        $parts = explode('/', $siteUrl);
        if (count($parts) >= 2) {
            array_pop($parts);
            $siteUrl = implode('/', $parts);
        }

        return 'https://' . rtrim($siteUrl, '/');
    }

    public function logoPath()
    {
        $attribute = $this->repository->getRootNodeAttribute('logo');
        if ($attribute instanceof eZContentObjectAttribute && $attribute->hasContent()) {
            /** @var eZImageAliasHandler $content */
            $content = $attribute->content();
            $original = $content->attribute('original');
            $data = $original['full_path'];
        } else {
            $data = '/extension/openpa_sensor/design/standard/images/logo_sensor.png';
        }
        return $data;
    }

    public function logoTitle()
    {
        return $this->getAttributeString('logo_title');
    }

    public function logoSubtitle()
    {
        return $this->getAttributeString('logo_subtitle');
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
        $currentModuleParams = $GLOBALS['eZRequestedModuleParams'];
        $request = array(
            'module' => $currentModuleParams['module_name'],
            'function' => $currentModuleParams['function_name'],
            'parameters' => $currentModuleParams['parameters'],
        );

        return $request['module'] == 'social_user';
    }

    public function attributeContacts()
    {
        $data = '';
        $attribute = $this->repository->getRootNodeAttribute('contacts');
        if ($attribute instanceof eZContentObjectAttribute) {
            $data = $attribute;
        }
        return $data;
    }

    public function attributeFooter()
    {
        $data = '';
        $attribute = $this->repository->getRootNodeAttribute('footer');
        if ($attribute instanceof eZContentObjectAttribute) {
            $data = $attribute;
        }
        return $data;
    }

    public function textCredits()
    {
        $versionFile = eZSys::rootDir() . '/VERSION';
        if (file_exists($versionFile)){
            $version = file_get_contents($versionFile);
            return 'OpenSegnalazioni <a href="https://gitlab.com/opencontent/opensegnalazioni/">' . $version . '</a> made with <i class="fa fa-heart"></i> by <a href="https://www.opencontent.it">Opencontent</a>';
        }

        return OpenPAINI::variable('CreditsSettings', 'Sensor');
    }

    public function googleAnalyticsId()
    {
        return OpenPAINI::variable('Seo', 'GoogleAnalyticsAccountID', false);
    }

    public function cookieLawUrl()
    {
        $href = 'sensor/info/cookie';
        eZURI::transformURI($href, false, 'full');
        return $href;
    }

    public function menu()
    {
        $infoChildren = array(
            array(
                'name' => ezpI18n::tr('sensor/menu', 'Faq'),
                'url' => 'sensor/info/faq',
                'has_children' => false,
            ),
            array(
                'name' => ezpI18n::tr('sensor/menu', 'Privacy'),
                'url' => 'sensor/info/privacy',
                'has_children' => false,
            ),
            array(
                'name' => ezpI18n::tr('sensor/menu', 'Termini di utilizzo'),
                'url' => 'sensor/info/terms',
                'has_children' => false,
            )
        );

        $hasAccess = eZUser::currentUser()->hasAccessTo('sensor', 'stat');
        if ($hasAccess['accessWord'] != 'no') {
            $infoChildren[] = array(
                'name' => ezpI18n::tr('sensor/chart', 'Statistiche'),
                'url' => 'sensor/stat',
                'highlight' => false,
                'has_children' => false
            );
        }

        $sensorIni = eZINI::instance('ocsensor.ini');
        $menuSegnalazioni = ezpI18n::tr('sensor/menu', 'Segnalazioni');
        if ($sensorIni->hasVariable('MenuSettings', 'Segnalazioni')) {
            $menuSegnalazioni = $sensorIni->variable('MenuSettings', 'Segnalazioni');
        }
        $menu = array(
            array(
                'name' => ezpI18n::tr('sensor/menu', 'Informazioni'),
                'url' => 'sensor/info',
                'highlight' => false,
                'has_children' => true,
                'children' => $infoChildren
            ),
            array(
                'name' => $menuSegnalazioni,
                'url' => 'sensor/posts',
                'highlight' => false,
                'has_children' => false
            )
        );
        if (eZUser::currentUser()->isRegistered()) {
            $menu[] = array(
                'name' => ezpI18n::tr('sensor/menu', 'Le mie attività'),
                'url' => 'sensor/dashboard',
                'highlight' => false,
                'has_children' => false
            );
            if ($sensorIni->hasVariable('SensorConfig', 'ShowInboxWidget')
                && $sensorIni->variable('SensorConfig', 'ShowInboxWidget') == 'menu'
                && $sensorIni->hasVariable('SocketSettings', 'Enabled')
                && $sensorIni->variable('SocketSettings', 'Enabled') == 'true') {
                $hasAccess = eZUser::currentUser()->hasAccessTo('sensor', 'manage');
                if ($hasAccess['accessWord'] != 'no') {
                    $menu[] = array(
                        'name' => ezpI18n::tr('sensor/menu', 'Inbox'),
                        'url' => 'sensor/inbox',
                        'highlight' => false,
                        'has_children' => false
                    );
                }
            }
            $menu[] = array(
                'name' => ezpI18n::tr('sensor/menu', 'Segnala'),
                'url' => 'sensor/add',
                'highlight' => true,
                'has_children' => false
            );
        }
        return $menu;
    }

    public function userMenu()
    {
        $userMenu = array(
            array(
                'name' => ezpI18n::tr('sensor/menu', 'Profilo'),
                'url' => 'user/edit',
                'highlight' => false,
                'has_children' => false
            ),
            array(
                'name' => ezpI18n::tr('sensor/menu', 'Notifiche'),
                'url' => 'notification/settings',
                'highlight' => false,
                'has_children' => false
            )
        );

        $hasAccess = eZUser::currentUser()->hasAccessTo('sensor', 'stat');
        if ($hasAccess['accessWord'] != 'no') {
            $userMenu[] = array(
                'name' => ezpI18n::tr('sensor/chart', 'Statistiche'),
                'url' => 'sensor/stat',
                'highlight' => false,
                'has_children' => false
            );
        }

        $hasAccess = eZUser::currentUser()->hasAccessTo('sensor', 'config');
        if ($hasAccess['accessWord'] == 'yes') {
            $userMenu[] = array(
                'name' => ezpI18n::tr('sensor/menu', 'Settings'),
                'url' => 'sensor/config',
                'highlight' => false,
                'has_children' => false
            );
        }

        if (in_array('ocwebhookserver', eZExtension::activeExtensions())) {
            $hasAccess = eZUser::currentUser()->hasAccessTo('webhook', 'admin');
            if ($hasAccess['accessWord'] == 'yes') {
                $userMenu[] = array(
                    'name' => ezpI18n::tr('sensor/menu', 'Webhooks'),
                    'url' => 'webhook/list',
                    'highlight' => false,
                    'has_children' => false
                );
            }
        }

        $userMenu[] = array(
            'name' => ezpI18n::tr('sensor/menu', 'Esci'),
            'url' => 'user/logout',
            'highlight' => false,
            'has_children' => false
        );
        return $userMenu;
    }

    public function bannerPath()
    {
        $data = false;
        $attribute = $this->repository->getRootNodeAttribute('banner');
        if ($attribute instanceof eZContentObjectAttribute && $attribute->hasContent()) {
            /** @var eZImageAliasHandler $content */
            $content = $attribute->content();
            $original = $content->attribute('original');
            $data = $original['full_path'];
        }
        return $data;
    }

    public function bannerTitle()
    {
        return $this->getAttributeString('banner_title');
    }

    public function bannerSubtitle()
    {
        return $this->getAttributeString('banner_subtitle');
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getAttributeString($identifier)
    {
        $data = '';
        $attribute = $this->repository->getRootNodeAttribute($identifier);
        if ($attribute instanceof eZContentObjectAttribute) {
            $data = self::replaceBracket($attribute->toString());
        }
        return $data;
    }

    /**
     * Replace [ ] with strong html tag
     * @param string $string
     * @return string
     */
    public static function replaceBracket($string)
    {
        $string = str_replace('[', '<strong>', $string);
        $string = str_replace(']', '</strong>', $string);
        return $string;
    }
}
