<div class="service_teaser vertical wow animated flipInX animated">
  {if $topic|has_attribute( 'image' )}
    <div class="service_photo">
      <figure style="background-image:url({$topic|attribute( 'image' ).content.original.full_path|ezroot(no)})"></figure>
    </div>
  {/if}
  <div class="service_details">
    <h2 class="section_header skincolored noborder">      
      <a class="dark" href="{concat( $topic.url_alias, '#partecipa' )|ezurl(no)}">{$topic.object.data_map.title.content|wash|bracket_to_strong}</a>
      <a href="{concat( $topic.url_alias, '#partecipa' )|ezurl(no)}" class="btn btn-primary btn-lg pull-right"><strong>Clicca qui per partecipare</strong></a>
      {if $topic|has_attribute( 'subtitle' )}
      <small>{$topic.object.data_map.subtitle.content|wash|bracket_to_strong}</small>
      {/if}
    </h2>
    {attribute_view_gui attribute=$topic.data_map.short_description}    
  </div>
</div>