{def $topic = $sensor.forum_container_node}
{if $topic.children_count|eq(1)}
  {include uri='design:sensor/parts/forum/home_block_single_forum.tpl' topic=$topic.children[0]}
{else}
  {include uri='design:sensor/parts/forum/home_block_multiple_forum.tpl' topic=$topic}
{/if}
{undef $topic}