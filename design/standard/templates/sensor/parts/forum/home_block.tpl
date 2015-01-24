{def $topic = $sensor.forum_container_node}
<div class="service_teaser vertical">
  {if $topic|has_attribute( 'image' )}
    <div class="service_photo">
      <figure style="background-image:url({$topic|attribute( 'image' ).content.original.full_path|ezroot(no)})"></figure>
    </div>
  {/if}
  <div class="service_details">
    <h2 class="section_header skincolored">
      <a href="{$topic.url_alias|ezurl(no)}">{$topic.object.data_map.title.content|wash|bracket_to_strong}</a>
      <small>{$topic.object.data_map.subtitle.content|wash|bracket_to_strong}</small>
    </h2>
    {if $topic.children_count|eq(1)}
      {attribute_view_gui attribute=$topic.object.data_map.short_description}
      {if $topic|has_attribute( 'description' )}
        <p class="text-right"><a href="{concat($topic.url_alias,'/(more)/1')|ezurl(no)}">Per saperne di pi&ugrave;</a></p>
      {/if}
      <a href="{$topic.url_alias|ezurl(no)}" class="btn btn-primary btn-lg btn-block">Partecipa alla discussione</a>
    {else}
      <a href="{$topic.url_alias|ezurl(no)}" class="btn btn-primary btn-lg btn-block">Partecipa alle discussioni</a>
    {/if}

  </div>
</div>
{undef $topic}