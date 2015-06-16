<?php

class SensorNotificationHelper
{
    /**
     * @var SensorPost
     */
    protected $post;

    protected function __construct( SensorPost $post = null )
    {
        $this->post = $post;
    }

    public static function instance( SensorPost $post = null )
    {
        return new SensorNotificationHelper( $post );
    }

    /**
     * @param eZNotificationEvent $event
     * @param array $parameters
     *
     * @return int
     * @throws Exception
     */
    public function handleEvent( eZNotificationEvent $event, array &$parameters )
    {
        if ( $this->post instanceof SensorPost )
        {
            $eventType = $event->attribute( 'data_text1' );
            $prefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';
            $eventIdentifier = str_replace( $prefix, '', $eventType );
            $searchRules = array( $prefix . $eventIdentifier );

            $participantIdList = $this->post->getParticipants( null, true );
            $ruleList = array();
            foreach ( $participantIdList as $roleGroup )
            {
                foreach ( $roleGroup['items'] as $item )
                {
                    $user = eZUser::fetch( $item['id'] );
                    if ( !$user instanceof eZUser )
                    {
                        continue;
                    }
                    $userInfo = SensorUserInfo::instance( $user );

                    foreach (
                        self::languageNotificationTypes(
                            $userInfo
                        ) as $languageNotification
                    )
                    {
                        if ( $languageNotification['parent'] == $eventIdentifier )
                        {
                            $searchRules[] = $prefix . $languageNotification['identifier'];
                        }
                    }

                    foreach (
                        self::transportNotificationTypes(
                            $userInfo
                        ) as $transportNotification
                    )
                    {
                        if ( $transportNotification['parent'] == $eventIdentifier )
                        {
                            $searchRules[] = $prefix . $transportNotification['identifier'];
                        }
                    }

                    $rules = eZCollaborationNotificationRule::fetchItemTypeList(
                        $searchRules,
                        array( $item['id'] ),
                        false
                    );

                    $ruleListItem = array(
                        'id' => $item['id'],
                        'email' => $user->attribute( 'email' ),
                        'whatsapp' => $userInfo->whatsAppId(),
                        'event_type' => $eventType
                    );
                    $hasCurrentRule = false;

                    $ruleListItem['transport'] = array();
                    foreach ( $rules as $rule )
                    {
                        if ( $rule['collab_identifier'] == $eventType )
                        {
                            $hasCurrentRule = true;
                        }
                        foreach (
                            self::languageNotificationTypes(
                                $userInfo
                            ) as $languageNotification
                        )
                        {
                            if ( $rule['collab_identifier'] == $prefix . $languageNotification['identifier'] )
                            {
                                $ruleListItem['language'] = str_replace(
                                    $eventType . ':',
                                    '',
                                    $rule['collab_identifier']
                                );
                            }
                        }
                        foreach (
                            self::transportNotificationTypes(
                                $userInfo
                            ) as $transportNotification
                        )
                        {
                            if ( $rule['collab_identifier'] == $prefix . $transportNotification['identifier'] )
                            {
                                $ruleListItem['transport'][] = str_replace(
                                    $eventType . ':',
                                    '',
                                    $rule['collab_identifier']
                                );
                            }
                        }
                    }

                    if ( !isset( $ruleListItem['language'] ) )
                    {
                        $ruleListItem['language'] = $userInfo->attribute(
                            'default_notification_language'
                        );
                    }
                    //                if ( !isset( $ruleListItem['transport'] ) )
                    //                {
                    //                    $ruleListItem['transport'] = $userInfo->attribute(
                    //                        'default_notification_transport'
                    //                    );
                    //                }
                    if ( $hasCurrentRule && count( $ruleListItem['transport'] ) > 0 )
                    {
                        foreach ( $ruleListItem['transport'] as $transport )
                        {
                            $ruleList[$transport][$roleGroup['role_id']][] = $ruleListItem;
                        }
                    }
                }
            }

            eZDebug::writeNotice( var_export( $ruleList, 1 ), __METHOD__ );

            foreach ( $ruleList as $transport => $userList )
            {
                if ( $transport == 'ezmail' )
                {
                    $this->createMailNotificationCollections( $event, $userList, $parameters );
                }

                if ( $transport == 'ezwhatsapp' )
                {
                    $this->createWhatsAppNotificationCollections( $event, $userList, $parameters );
                }
            }
            return eZNotificationEventHandler::EVENT_HANDLED;
        }
        else
        {
            eZDebug::writeError( "Post not found", __METHOD__ );
            return eZNotificationEventHandler::EVENT_SKIPPED;
        }
    }

    protected function createMailNotificationCollections( eZNotificationEvent $event, $userCollection, &$parameters )
    {
        $db = eZDB::instance();
        $db->begin();

        $tpl = eZTemplate::factory();
        $tpl->resetVariables();

        foreach( $userCollection as $participantRole => $collectionItems )
        {
            $templateName = $this->notificationMailTemplate( $participantRole );
            $templatePath = 'design:sensor/mail/' . $templateName;

            $tpl->setVariable( 'collaboration_item', $this->post->getCollaborationItem() );
            $tpl->setVariable( 'collaboration_participant_role', $participantRole );
            $tpl->setVariable( 'collaboration_item_status', $this->post->getCollaborationItem()->attribute( SensorPost::COLLABORATION_FIELD_STATUS ) );
            $tpl->setVariable( 'sensor_post', $this->post );
            $tpl->setVariable( 'object', $this->post->objectHelper->getContentObject() );
            $tpl->setVariable( 'node', $this->post->objectHelper->getContentObject()->attribute( 'main_node' ) );

            $tpl->fetch( $templatePath );

            $body = $tpl->variable( 'body' );
            $subject = $tpl->variable( 'subject' );

            if ( !empty( $body ) )
            {
                $tpl->setVariable( 'title', $subject );
                $tpl->setVariable( 'content', $body );
                $templateResult = $tpl->fetch( 'design:sensor/mail/mail_pagelayout.tpl' );

                if ( $tpl->hasVariable( 'message_id' ) )
                {
                    $parameters['message_id'] = $tpl->variable( 'message_id' );
                }
                if ( $tpl->hasVariable( 'references' ) )
                {
                    $parameters['references'] = $tpl->variable( 'references' );
                }
                if ( $tpl->hasVariable( 'reply_to' ) )
                {
                    $parameters['reply_to'] = $tpl->variable( 'reply_to' );
                }
                if ( $tpl->hasVariable( 'from' ) )
                {
                    $parameters['from'] = $tpl->variable( 'from' );
                }
                if ( $tpl->hasVariable( 'content_type' ) )
                {
                    $parameters['content_type'] = $tpl->variable( 'content_type' );
                }
                else
                {
                    $parameters['content_type'] = 'text/html';
                }

                $collection = eZNotificationCollection::create(
                    $event->attribute( 'id' ),
                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    'ezmail'
                );

                $collection->setAttribute( 'data_subject', $subject );
                $collection->setAttribute( 'data_text', $templateResult );
                $collection->store();
                foreach ( $collectionItems as $collectionItem )
                {
                    $collection->addItem( $collectionItem['email'] );
                }
            }
        }

        $db->commit();
    }

    protected function createWhatsAppNotificationCollections( eZNotificationEvent $event, $userCollection, &$parameters )
    {
        if ( class_exists( 'OCWhatsAppConnector' ) )
        {

            $db = eZDB::instance();
            $db->begin();

            $tpl = eZTemplate::factory();
            $tpl->resetVariables();

            foreach ( $userCollection as $participantRole => $collectionItems )
            {
                $templateName = $this->notificationMailTemplate( $participantRole );
                $templatePath = 'design:sensor/whatsapp/' . $templateName;

                $tpl->setVariable( 'collaboration_item', $this->post->getCollaborationItem() );
                $tpl->setVariable( 'collaboration_participant_role', $participantRole );
                $tpl->setVariable(
                    'collaboration_item_status',
                    $this->post->getCollaborationItem()->attribute(
                        SensorPost::COLLABORATION_FIELD_STATUS
                    )
                );
                $tpl->setVariable( 'post_url', $this->post->objectHelper->getPostUrl() );
                $tpl->setVariable( 'object', $this->post->objectHelper->getContentObject() );
                $tpl->setVariable(
                    'node',
                    $this->post->objectHelper->getContentObject()->attribute( 'main_node' )
                );

                $message = trim( $tpl->fetch( $templatePath ) );

                if ( $message != '' )
                {

                    eZDebug::writeNotice( $message, __METHOD__ );

                    //                $collection = eZNotificationCollection::create(
                    //                    $event->attribute( 'id' ),
                    //                    eZCollaborationNotificationHandler::NOTIFICATION_HANDLER_ID,
                    //                    'ezwhatsapp'
                    //                );
                    //                $collection->setAttribute( 'data_text', $templateResult );
                    //                foreach ( $collectionItems as $collectionItem )
                    //                {
                    //                    $collection->addItem( $collectionItem['whatsapp'] );
                    //                }
                    $waUserId = SensorHelper::factory()->getWhatsAppUserId();
                    try
                    {
                        $wa = OCWhatsAppConnector::instanceFromContentObjectId( $waUserId );
                        $wa->connectAndLogin();
                        foreach ( $collectionItems as $collectionItem )
                        {
                            $wa->whatsProt->sendMessage( $collectionItem['whatsapp'], $message );
                        }
                    }
                    catch ( Exception $e )
                    {
                        eZDebug::writeError(
                            $e->getMessage() . ' ' . $e->getTraceAsString(),
                            __METHOD__
                        );
                    }
                }
            }

            $db->commit();
        }
    }

    protected function notificationMailTemplate( $participantRole )
    {
        if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_APPROVER )
        {
            return 'approver.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_AUTHOR )
        {
            return 'author.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_OBSERVER )
        {
            return 'observer.tpl';
        }
        else if ( $participantRole == eZCollaborationItemParticipantLink::ROLE_OWNER )
        {
            return 'owner.tpl';
        }
        else
            return false;
    }

    public function notificationTypes()
    {
        return array_merge(
            $this->postNotificationTypes(),
            $this->transportNotificationTypes(),
            $this->languageNotificationTypes()
        );
    }

    public function postNotificationTypes()
    {
        $postNotificationTypes = array();

        $postNotificationTypes[] = array(
            'identifier' => 'on_create',
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Creazione di una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Ricevi una notifica alla creazione di una segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_assign',
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Assegnazione di una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Ricevi una notifica quando una tua segnalazione è assegnata a un responsabile'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_add_comment',
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Commento pubblico a una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Ricevi una notifica quando è aggiunto un commento pubblico ad una tua segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_fix',
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Intervento terminato'
            ),
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                "Ricevi una notifica quando un responsabile ha completato l'attività che riguarda una tua segnalazione"
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'identifier' => 'on_close',
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Chiusura di una segnalazione'
            ),
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                "Ricevi una notifica quando una tua segnalazione è stata chiusa"
            ),
            'group' => 'standard'
        );

        if ( OpenPAINI::variable( 'SensorConfig', 'AuthorCanReopen', 'disabled' ) == 'enabled' )
        {
            $postNotificationTypes[] = array(
                'identifier' => 'on_reopen',
                'name' => ezpI18n::tr(
                    'openpa_sensor/notification',
                    'Riapertura di una segnalazione'
                ),
                'description' => ezpI18n::tr(
                    'openpa_sensor/notification',
                    "Ricevi una notifica alla riapertura di una tua segnalazione"
                ),
                'group' => 'standard'
            );
        }

        return $postNotificationTypes;
    }

    protected function languageNotificationTypes( SensorUserInfo $userInfo = null )
    {
        if ( $userInfo === null )
        {
            $userInfo = SensorUserInfo::current();
        }
        $languagesNotificationTypes = array();
        /** @var eZContentLanguage[] $languages */
        $languages = eZContentLanguage::prioritizedLanguages();
        $defaultLanguageCode = $userInfo->attribute( 'default_notification_language' );
        if ( count( $languages ) > 1 )
        {
            foreach( self::postNotificationTypes() as $type )
            {
                foreach ( $languages as $language )
                {
                    $languagesNotificationTypes[] = array(
                        'name' => $language->attribute( 'name' ),
                        'identifier' => $type['identifier'] . ':' . $language->attribute( 'locale' ),
                        'description' => ezpI18n::tr(
                            'openpa_sensor/notification',
                            'In che lingua vuoi ricevere le notifiche?'
                        ),
                        'language_code' => $language->attribute( 'locale' ),
                        'default_language_code' => $defaultLanguageCode,
                        'parent' => $type['identifier'],
                        'group' => 'language'
                    );
                }
            }
        }
        return $languagesNotificationTypes;
    }

    protected function transportNotificationTypes( SensorUserInfo $userInfo = null )
    {
        if ( $userInfo === null )
        {
            $userInfo = SensorUserInfo::current();
        }
        $transportNotificationTypes = array();
        $defaultTransport = $userInfo->attribute( 'default_notification_transport' );
        foreach( self::postNotificationTypes() as $type )
        {
            $transportNotificationTypes[] = array(
                'name' => 'Email',
                'identifier' => $type['identifier'] . ':ezmail',
                'description' => ezpI18n::tr(
                    'openpa_sensor/notification',
                    'Ricevi la notifica via mail'
                ),
                'transport' => 'ezmail',
                'default_transport' => $defaultTransport,
                'parent' => $type['identifier'],
                'group' => 'transport',
                'enabled' => $defaultTransport == 'ezmail'
            );


            $transportNotificationTypes[] = array(
                'name' => 'WhatsApp',
                'identifier' => $type['identifier'] . ':ezwhatsapp',
                'description' => ezpI18n::tr(
                    'openpa_sensor/notification',
                    'Ricevi la notifica via WhatsApp'
                ),
                'transport' => 'ezwhatsapp',
                'default_transport' => $defaultTransport,
                'parent' => $type['identifier'],
                'group' => 'transport',
                'enabled' => $type['identifier'] != 'on_create' && $userInfo->whatsAppId() //@todo
            );
        }
        return $transportNotificationTypes;
    }

    public function storeDefaultNotificationRules( $userId )
    {
        try
        {
            $userInfo = SensorUserInfo::instance( eZUser::fetch( $userId ) );
            $prefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';

            $transport = $userInfo->attribute( 'default_notification_transport' );
            $language = $userInfo->attribute( 'default_notification_language' );
            $rules = array( 'on_create', 'on_assign', 'on_close' );

            $db = eZDB::instance();
            $db->begin();
            foreach ( $rules as $rule )
            {
                $notificationRule = eZCollaborationNotificationRule::create(
                    $prefix . $rule,
                    $userId
                );
                $notificationRule->store();
                $notificationRule = eZCollaborationNotificationRule::create(
                    $prefix . $rule . ':' . $transport,
                    $userId
                );
                $notificationRule->store();
                $notificationRule = eZCollaborationNotificationRule::create(
                    $prefix . $rule . ':' . $language,
                    $userId
                );
                $notificationRule->store();
            }
            $db->commit();
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
        }
    }

}