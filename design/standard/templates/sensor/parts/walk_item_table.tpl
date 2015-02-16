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
  <td>              
	{foreach $item.node.object.available_languages as $language}
	  {foreach fetch( 'content', 'translation_list' ) as $locale}
		{if $locale.locale_code|eq($language)}
		  <img src="{$locale.locale_code|flag_icon()}" />
		{/if}
	  {/foreach}
	{/foreach}
  </td>
  <td width="1">{include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$item.node redirect_if_discarded=$redirect_if_discarded redirect_after_publish=$redirect_after_publish}</td>
  <td width="1">{include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$item.node}</td>
  <td width="1">
    {if $item.children|count()|gt(0)}
      <a href={concat("/websitetoolbar/sort/",$item.node.node_id)|ezurl()}><i class="fa fa-sort-alpha-asc "></i>
    {/if}
  </td>

  <td width="1">
    {if and( $item.children|count()|gt(0), is_set( $insert_child_class ) )}
      <a title="{'Aggiungi'|i18n('openpa_sensor/config')}  {$item.children[0].node.class_name} in {$item.node.name|wash()}" href="{concat('openpa/add/', $item.children[0].node.class_identifier, '/?parent=',$item.node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i></a>
    {elseif is_set( $insert_child_class )|not()}
      <a title="{'Aggiungi'|i18n('openpa_sensor/config')} {$item.node.class_name} in {$item.node.name|wash()}" href="{concat('openpa/add/', $item.node.class_identifier, '/?parent=',$item.node.node_id)|ezurl(no)}"><i class="fa fa-plus"></i></a>
    {/if}
  </td>

</tr>
{if $item.children|count()|gt(0)}
  {set $recursion = $recursion|inc()}
  {foreach $item.children as $item_child}
  {include name=itemtree uri='design:sensor/parts/walk_item_table.tpl' redirect_if_discarded=$redirect_if_discarded redirect_after_publish=$redirect_after_publish item=$item_child recursion=$recursion insert_child_class=is_set( $insert_child_class )}
  {/foreach}
{/if}