<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Sensor Force Owner for a ticket\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions(
    '[id:][user_id:]',
    '',
    array(
        'id' => 'Object id',
        'user_id' => 'User id'
    )
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

OpenPALog::setOutputLevel(OpenPALog::ALL);

try {
    if (isset($options['id']) && isset($options['user_id'])) {
        $newOwnerId = $options['user_id'];

        $repository = OpenPaSensorRepository::instance();
        $post = $repository->getPostService()->loadPost($objectId);

        $action = new \Opencontent\Sensor\Api\Action\Action();
        $action->identifier = 'assign';
        $action->setParameter('participant_ids', [$newOwnerId]);

        $repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
            $repository,
            $action,
            $post,
            $repository->getCurrentUser()
        );
    }


    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}