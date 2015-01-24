{ezcss_require('font-awesome-animation.min.css')}

{if count($available_dashboard)|gt(1)}
<p class="text-right">
{foreach $available_dashboard as $name => $dashboard}
  <a class="btn btn-lg{if $dashboard|eq($current_dashboard)} btn-success{else} btn-info{/if}" href="{concat('sensor/dashboard/',$dashboard)|ezurl(no)}">{$name|wash()}</a>
{/foreach}
</p>
{/if}

{def $sensor = sensor_root_handler()}
{if and( $sensor.post_is_enabled, $current_dashboard|eq('post'))}
  {include uri='design:sensor/parts/post/dashboard.tpl'}
{/if}

{if and( $sensor.forum_is_enabled, $current_dashboard|eq('forum'))}
  {include uri='design:sensor/parts/forum/dashboard.tpl'}
{/if}

{if and( $sensor.survey_is_enabled, $current_dashboard|eq('survey'))}
  {include uri='design:sensor/parts/survey/dashboard.tpl'}
{/if}