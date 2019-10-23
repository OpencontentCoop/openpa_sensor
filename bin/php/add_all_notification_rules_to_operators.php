<?php

require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Sensor Add all notification rules to operators\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

OpenPALog::setOutputLevel(OpenPALog::ALL);
/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

try {
    $repository = OpenPaSensorRepository::instance();

    function loadAllOperators(OpenPaSensorRepository $repository, $cursor, &$operators)
    {
        $results = $repository->getOperatorService()->loadOperators(false, \Opencontent\Sensor\Api\SearchService::MAX_LIMIT, $cursor);
        $operators = array_merge($operators, $results['items']);
        if ($results['next'] && $results['next'] != $cursor){
            loadAllOperators($repository, $results['next'], $operators);
        }
    }

    $operators = [];
    loadAllOperators($repository, '*', $operators);
    foreach ($operators as $operator) {
        foreach ($repository->getNotificationService()->getNotificationTypes() as $notificationType){
            $repository->getNotificationService()->addUserToNotification($operator, $notificationType);
        }
    }


    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}