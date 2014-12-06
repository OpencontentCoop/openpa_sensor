{default html_class='full' placeholder=false()}

{if $placeholder}
    <label>{$placeholder}</label>
{/if}

{if is_set( $attribute_base )|not()}
  {def $attribute_base = 'ContentObjectAttribute'}
{/if}

{def $sensor = sensor_root_handler()     
     $areas = $sensor.areas.tree}

{if and( count( $areas )|eq(1), is_set( $areas[0]['children'] ))}
    {set $areas = $areas[0]['children']}
{/if}

<input type="hidden" name="single_select_{$attribute.id}" value="1" />
{if ne( count( $areas ), 0)}
    <select {if openpaini( 'SensorConfig', 'MoveMarkerOnSelectArea', 'disabled' )|eq('enabled')}id="poi"{/if} class="{$html_class}" name="{$attribute_base}_data_object_relation_list_{$attribute.id}[]">
        <option>Non specificato</option>
        {foreach $areas as $area}
            {include name=areatree uri='design:sensor/parts/walk_item_option.tpl' item=$area recursion=0 attribute=$attribute}
        {/foreach}
    </select>
{/if}

{/default}
