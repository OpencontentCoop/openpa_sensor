{ezcss_require('font-awesome-animation.min.css')}

{def $sensor = sensor_root_handler()}
{if $sensor.post_is_enabled}
  {include uri='design:sensor/parts/post/dashboard.tpl'}
{/if}

{if $sensor.forum_is_enabled}
  {include uri='design:sensor/parts/forum/dashboard.tpl'}
{/if}

{if $sensor.survey_is_enabled}
  {include uri='design:sensor/parts/survey/dashboard.tpl'}
{/if}