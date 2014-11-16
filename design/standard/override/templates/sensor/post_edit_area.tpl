{default html_class='full' placeholder=false()}

{if $placeholder}
    <label>{$placeholder}</label>
{/if}

{def $sensor = sensor_root_handler()
     $attribute_base = 'ContentObjectAttribute'
     $areas = $sensor.areas.tree}

{if and( count( $areas )|eq(1), is_set( $areas[0]['children'] ))}
    {set $areas = $areas[0]['children']}
{/if}

<input type="hidden" name="single_select_{$attribute.id}" value="1" />
{if ne( count( $areas ), 0)}
    <select id="poi" class="{$html_class}" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]">
        <option></option>
        {foreach $areas as $area}
            {include name=areatree uri='design:sensor/parts/walk_area.tpl' area=$area recursion=0}
        {/foreach}
    </select>
{/if}

{/default}
