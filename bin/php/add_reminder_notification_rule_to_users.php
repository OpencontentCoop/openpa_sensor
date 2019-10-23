<?php

require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Sensor Add newsletter notification rules to users\n\n"),
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

    $parameters = [
        'Depth' => 1,
        'DepthOperator' => 'eq',
        'SortBy' => ['contentobject_id', 'asc']
    ];
    $userCount = $repository->getUserRootNode()->subTreeCount($parameters);
    $cli->output("Found $userCount users");

    $length = 50;
    $parameters['Limit'] = $length;
    $parameters['Offset'] = 0;

    $notification = $repository->getNotificationService()->getNotificationByIdentifier('reminder');
    if (!$notification){
        throw new Exception("Notification reminder not found");
    }
    do {
        $users = $repository->getUserRootNode()->subTree($parameters);

        foreach ($users as $user) {
            $cli->output($user->attribute('name'));
            $sensorUser = $repository->getUserService()->loadUser($user->attribute('contentobject_id'));
            $repository->getNotificationService()->addUserToNotification($sensorUser, $notification);
        }

        $parameters['Offset'] += $length;
    } while (count($users) == $length);

    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}