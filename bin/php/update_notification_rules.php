<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Sensor Update notification rules for users\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

OpenPALog::setOutputLevel(OpenPALog::ALL);

try {
    $repository = OpenPaSensorRepository::instance();
    /** @var eZCollaborationNotificationRule[] $rules */
    $rules = eZPersistentObject::fetchObjectList(
        eZCollaborationNotificationRule::definition(),
        null,
        array(
            'collab_identifier' => $repository->getSensorCollaborationHandlerTypeString()
        ),
        null,
        null,
        true
    );

    foreach ($rules as $rule) {
        $userId = $rule->attribute('user_id');
        $repository->addDefaultNotificationsToUser($userId);
        $rule->remove();
    }

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}