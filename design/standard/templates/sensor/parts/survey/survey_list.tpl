{def $col-width=6 $modulo=2}

{if is_set($current_user)|not()}
    {def $current_user = fetch( 'user', 'current_user' )}
{/if}

{if is_set($sensor)|not()}
    {def $sensor = sensor_root_handler()}
{/if}

<div class="row">
    {foreach $sensor.surveys as $survey}
    <div class="col-md-{$col-width}">
        {include uri='design:sensor/parts/survey/single_survey.tpl' survey=$survey current_user=$current_user}
    </div>
    {delimiter modulo=$modulo}
</div>
<div class="row">
    {/delimiter}
    {/foreach}
</div>