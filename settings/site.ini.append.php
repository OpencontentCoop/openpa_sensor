<?php /*

[RegionalSettings]
TranslationExtensions[]=openpa_sensor

[TemplateSettings]
ExtensionAutoloadPath[]=openpa_sensor

[RoleSettings]
PolicyOmitList[]=sensor/use

[Event]
Listeners[]=content/cache@SensorModuleFunctions::onClearObjectCache


[Cache]
CacheItems[]=sensor

[Cache_sensor]
name=Sensor cache
id=sensor
tags[]=content
path=sensor


*/ ?>
