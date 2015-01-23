<section class="hgroup">
  <h1>{$node.name|wash()|bracket_to_strong}</h1>
</section>

<section class="hgroup">
  {attribute_view_gui attribute=$node.data_map.description}
</section>

{include uri='design:sensor/parts/forum/topic_list.tpl' node=$node}