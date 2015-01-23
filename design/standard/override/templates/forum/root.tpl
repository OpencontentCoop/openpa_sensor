{* se si usano banner in forum/slideshow scommentare
<section class="call_to_action">
  <h3>{attribute_view_gui attribute=$node.data_map.title}</h3>
  <h4>{attribute_view_gui attribute=$node.data_map.subtitle}</h4>
  <a href="#" class="btn btn-primary btn-lg">{attribute_view_gui attribute=$node.data_map.button_title}</a></section>
</section>
*}

{if or( $node.children_count|gt(1), is_set( $view_parameters.more ))}
  <section class="hgroup">
  {attribute_view_gui attribute=$node.data_map.description}
  </section>
{/if}

<section class="service_teasers">

  {foreach $node.children as $item}
    {include name=dimmi_item uri='design:sensor/parts/forum/forum_list_item.tpl' node=$item total=$node.children_count more=is_set( $view_parameters.more )}
  {/foreach}

  {if and( $node.children_count|eq(1), is_set( $view_parameters.more )|not() )}
    {include uri='design:sensor/parts/forum/topic_list.tpl' node=$node.children[0]}
  {/if}

</section>

