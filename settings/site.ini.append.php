<?php /*

[RegionalSettings]
TranslationExtensions[]=openpa_sensor

[TemplateSettings]
ExtensionAutoloadPath[]=openpa_sensor

[RoleSettings]
PolicyOmitList[]=sensor/use

[Event]
Listeners[]=content/cache@SensorModuleFunctions::onClearObjectCache
Listeners[]=sensor/config_param@ObjectHandlerServiceControlSensor::onConfigParams
Listeners[]=sensor/set_status@ObjectHandlerServiceControlSensor::onSetStatus
Listeners[]=sensor/make_private@ObjectHandlerServiceControlSensor::onMakePrivate
Listeners[]=sensor/moderate@ObjectHandlerServiceControlSensor::onModerate
Listeners[]=sensor/set_areas@ObjectHandlerServiceControlSensor::onSetAreas
Listeners[]=sensor/set_categories@ObjectHandlerServiceControlSensor::onSetCategories
Listeners[]=sensor/user_by_categories@ObjectHandlerServiceControlSensor::getUserByCategories


[Cache]
CacheItems[]=sensor

[Cache_sensor]
name=Sensor cache
id=sensor
tags[]=content
path=sensor


*/ ?>
