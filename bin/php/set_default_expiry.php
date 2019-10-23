<?php
require 'autoload.php';

$script = eZScript::instance(array('description' => ("OpenPA Sensor Set default expiry for all ticket\n\n"),
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

    /** @var eZCollaborationItem[] $items */
    $items = eZPersistentObject::fetchObjectList(
        eZCollaborationItem::definition(),
        null,
        array('type_identifier' => $repository->getSensorCollaborationHandlerTypeString())
    );

    foreach ($items as $item) {
        if ($item->attribute(\Opencontent\Sensor\Legacy\PostService::COLLABORATION_FIELD_EXPIRY) == '') {
            $post = $repository->getPostService()->loadPost($objectId);

            $action = new \Opencontent\Sensor\Api\Action\Action();
            $action->identifier = 'set_expiry';
            $action->setParameter('expiry_days', OpenPAINI::variable('SensorConfig', 'DefaultPostExpirationDaysInterval', 15));

            $repository->getActionService()->loadActionDefinitionByIdentifier($action->identifier)->run(
                $repository,
                $action,
                $post,
                $repository->getCurrentUser()
            );
        }
    }


    $script->shutdown();
} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}