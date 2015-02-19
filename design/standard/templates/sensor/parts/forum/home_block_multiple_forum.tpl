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
    {attribute_view_gui attribute=$topic.data_map.short_description}
    <a href="{$topic.url_alias|ezurl(no)}" class="btn btn-primary btn-lg btn-block">Partecipa alle discussioni</a>
  </div>
</div>