{def $sensor = sensor_root_handler()}

{def $count = 0}
{if $sensor.post_is_enabled}
  {set $count = $count|inc()}
{/if}
{if $sensor.forum_is_enabled}
  {set $count = $count|inc()}
{/if}
{if $sensor.survey_is_enabled}
  {set $count = $count|inc()}
{/if}
{if $count|gt(0)}
  {def $col = 12|div($count)}
<section class="hgroup">
  <div class="row">

    {if $sensor.post_is_enabled}
    <div class="col-sm-{$col} col-md-{$col}">
      {include uri='design:sensor/parts/post/home_block.tpl'}
    </div>
    {/if}

    {if $sensor.forum_is_enabled}
      <div class="col-sm-{$col} col-md-{$col}">
        {include uri='design:sensor/parts/forum/home_block.tpl'}
      </div>
    {/if}

    {if $sensor.survey_is_enabled}
      <div class="col-sm-{$col} col-md-{$col}">
        {include uri='design:sensor/parts/survey/home_block.tpl'}
      </div>
    {/if}

	</div>
</section>
{/if}
{undef $sensor}
