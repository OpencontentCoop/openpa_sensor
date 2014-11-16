<option value="{$area.node.contentobject_id}" style="padding-left:{5|sum( $recursion|mul(7) )}px;"
    {if ne( count( $attribute.content.relation_list ), 0)}
        {foreach $attribute.content.relation_list as $item}
            {if eq( $item.contentobject_id, $area.node.contentobject_id )} selected="selected"{break}{/if}
        {/foreach}
    {/if}
    >
{$area.node.name|wash}
</option>
{if $area.children|count()|gt(0)}
    {set $recursion = $recursion|inc()}
    {foreach $area.children as $subarea}
        {include name=areachildren uri='design:sensor/parts/walk_area.tpl' area=$subarea recursion=$recursion}
    {/foreach}
{/if}
