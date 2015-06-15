<?php

class SensorNotificationHelper
{
    protected $post;

    protected function __construct( SensorPost $post )
    {
        $this->post = $post;
    }

    public static function instance( SensorPost $post )
    {
        return new SensorNotificationHelper( $post );
    }

    public function handleEvent( eZNotificationEvent $event, array &$parameters )
    {
        $eventType = $event->attribute( 'data_text1' );
        $prefix = SensorHelper::factory()->getSensorCollaborationHandlerTypeString() . '_';
        $eventIdentifier = str_replace( $prefix, '', $eventType );
        $searchRules = array( 'standard' => $prefix . $eventIdentifier );
        foreach( self::languageNotificationTypes() as $languageNotification )
        {
            if ( $languageNotification['parent'] == $eventIdentifier )
            {
                $searchRules[] = $prefix . $languageNotification['identifier'];
            }
        }

        foreach( self::transportNotificationTypes() as $transportNotification )
        {
            if ( $transportNotification['parent'] == $eventIdentifier )
            {
                $searchRules[] = $prefix . $transportNotification['identifier'];
            }
        }

        $participantIdList = $this->post->getParticipants( null, true );
        $ruleList = array();
        foreach( $participantIdList as $roleGroup )
        {
            $ruleList[$roleGroup['role_id']] = array();
            foreach( $roleGroup['items'] as $item )
            {
                $user = eZUser::fetch( $item['id'] );
                if ( !$user instanceof eZUser )
                {
                    continue;
                }
                $sensorUser = SensorUserInfo::instance( $user );
                $rules = eZCollaborationNotificationRule::fetchItemTypeList(
                    $searchRules,
                    array( $item['id'] ),
                    false
                );
                $ruleListItem = array(
                    'id' => $item['id'],
                    'email' => $user->attribute( 'email' ),
                    'whatsapp' => $sensorUser->whatsAppId()
                );
                foreach ( $rules as $rule )
                {
                    foreach ( self::languageNotificationTypes() as $languageNotification )
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
                    foreach ( self::transportNotificationTypes() as $transportNotification )
                    {
                        if ( $rule['collab_identifier'] == $prefix . $transportNotification['identifier'] )
                        {
                            $ruleListItem['transport'] = str_replace(
                                $eventType . ':',
                                '',
                                $rule['collab_identifier']
                            );
                        }
                    }
                }
                if ( !isset( $ruleListItem['language'] ) )
                {
                    $ruleListItem['language'] = $sensorUser->attribute(
                        'default_notification_language'
                    );
                }
                if ( !isset( $ruleListItem['transport'] ) )
                {
                    $ruleListItem['transport'] = $sensorUser->attribute(
                        'default_notification_transport'
                    );
                }
                $ruleList[$roleGroup['role_id']][] = $ruleListItem;
            }
        }
print_r($ruleList);die();
        $currentEventIdentifierUserIdList = array();
        foreach ( $ruleList as $rule )
        {
            $currentEventIdentifierUserIdList[] = $rule['user_id'];
        }
        if ( empty( $currentEventIdentifierUserIdList ) )
        {
            return eZNotificationEventHandler::EVENT_SKIPPED;
        }

        $participantList = $this->post->getParticipants( null, true );

        $userCollection = array();
        foreach( $participantList as $participantRole  )
        {
            $userCollection[$participantRole['role_id']] = array();
            foreach( $participantRole['items'] as $participant )
            {
                if ( in_array( $participant['id'], $currentEventIdentifierUserIdList ) )
                {
                    $userCollection[$participantRole['role_id']][] = $participant['id'];
                }
            }
        }

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
                    eZCollaborationNotificationHandler::TRANSPORT
                );

                $collection->setAttribute( 'data_subject', $subject );
                $collection->setAttribute( 'data_text', $templateResult );
                $collection->store();
                foreach ( $collectionItems as $collectionItem )
                {
                    $skip = false;

                    $user = eZUser::fetch( $collectionItem );

                    if ( !$user instanceof eZUser )
                    {
                        $skip = true;
                    }

                    if ( class_exists( 'OCWhatsAppConnector' ) )
                    {
                        if ( strpos( $collectionItem['email'], '@s.whatsapp.net' ) !== false )
                        {
                            $skip = true;
                        }
                    }
                    if ( !$skip )
                    {
                        $collection->addItem( $collectionItem['email'] );
                    }
                }
            }
        }

        $db->commit();


        return eZNotificationEventHandler::EVENT_HANDLED;
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

    public static function notificationTypes()
    {
        return array_merge(
            self::postNotificationTypes(),
            self::transportNotificationTypes(),
            self::languageNotificationTypes()
        );
    }

    protected static function postNotificationTypes()
    {
        $postNotificationTypes = array();

        $postNotificationTypes[] = array(
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Creazione di una segnalazione'
            ),
            'identifier' => 'on_create',
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Ricevi una notifica alla creazione di una segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Assegnazione di una segnalazione'
            ),
            'identifier' => 'on_assign',
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Ricevi una notifica quando una tua segnalazione è assegnata a un responsabile'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Commento pubblico a una segnalazione'
            ),
            'identifier' => 'on_add_comment',
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Ricevi una notifica quando è aggiunto un commento pubblico ad una tua segnalazione'
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Intervento terminato'
            ),
            'identifier' => 'on_fix',
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                "Ricevi una notifica quando un responsabile ha completato l'attività che riguarda una tua segnalazione"
            ),
            'group' => 'standard'
        );

        $postNotificationTypes[] = array(
            'name' => ezpI18n::tr(
                'openpa_sensor/notification',
                'Chiusura di una segnalazione'
            ),
            'identifier' => 'on_close',
            'description' => ezpI18n::tr(
                'openpa_sensor/notification',
                "Ricevi una notifica quando una tua segnalazione è stata chiusa"
            ),
            'group' => 'standard'
        );

        if ( OpenPAINI::variable( 'SensorConfig', 'AuthorCanReopen', 'disabled' ) == 'enabled' )
        {
            $postNotificationTypes[] = array(
                'name' => ezpI18n::tr(
                    'openpa_sensor/notification',
                    'Riapertura di una segnalazione'
                ),
                'identifier' => 'on_reopen',
                'description' => ezpI18n::tr(
                    'openpa_sensor/notification',
                    "Ricevi una notifica alla riapertura di una tua segnalazione"
                ),
                'group' => 'standard'
            );
        }

        return $postNotificationTypes;
    }

    protected static function languageNotificationTypes()
    {
        $languagesNotificationTypes = array();
        /** @var eZContentLanguage[] $languages */
        $languages = eZContentLanguage::prioritizedLanguages();
        $defaultLanguageCode = SensorUserInfo::current()->attribute( 'default_notification_language' );
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

    protected static function transportNotificationTypes()
    {
        $transportNotificationTypes = array();
        if ( SensorUserInfo::current()->whatsAppId() )
        {
            $defaultTransport = SensorUserInfo::current()->attribute( 'default_notification_transport' );
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
                    'group' => 'transport'
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
                    'group' => 'transport'
                );
            }
        }
        return $transportNotificationTypes;
    }
}