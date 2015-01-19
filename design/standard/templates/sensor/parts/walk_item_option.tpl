<option value="{$item.node.contentobject_id}" style="padding-left:{5|sum( $recursion|mul(10) )}px;{if $recursion|eq(0)}font-weight: bold;{/if}"
    {if ne( count( $attribute.content.relation_list ), 0)}
        {foreach $attribute.content.relation_list as $relation}
            {if eq( $relation.contentobject_id, $item.node.contentobject_id )} selected="selected"{break}{/if}
        {/foreach}
    {/if}
    >{$item.node.name|wash}</option>
{if $item.children|count()|gt(0)}
    {set $recursion = $recursion|inc()}
    {foreach $item.children as $subitem}
        {include name=itemchildren uri='design:sensor/parts/walk_item_option.tpl' item=$subitem recursion=$recursion attribute=$attribute}
    {/foreach}
{/if}
