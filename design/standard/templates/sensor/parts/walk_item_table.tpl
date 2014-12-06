<tr>
  <td>
    {*<a href="{$item.node.url_alias|ezurl(no)}">*}
      <span style="padding-left:{$recursion|mul(20)}px">
        {if $recursion|eq(0)}<strong>{/if}
        {$item.node.name|wash()}
        {if $recursion|eq(0)}</strong>{/if}
      </span>
    {*</a>*}
  </td>
  <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$item.node}</td>
  <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$item.node}</td>
  <td width="1"><a href="{concat('openpa/add/', $item.node.class_identifier, '/?parent=',$item.node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i></a></td>  
</tr>
{if $item.children|count()|gt(0)}
  {set $recursion = $recursion|inc()}
  {foreach $item.children as $item_child}
  {include name=itemtree uri='design:sensor/parts/walk_item_table.tpl' item=$item_child recursion=$recursion}
  {/foreach}
{/if}