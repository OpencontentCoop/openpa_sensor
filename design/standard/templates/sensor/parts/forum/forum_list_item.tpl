<div class="row service_teaser">

  
  <div class="service_photo col-sm-4 col-md-4 hidden-xs hidden-sm">
    {if $node|has_attribute('image')}
      <figure style="background-image:url({$node|attribute('image').content.large.full_path|ezroot(no)})"></figure>
    {/if}

    <section class="call_to_action">
      <a href={$node.url_alias|ezurl()} class="btn btn-primary btn-lg btn-block">Partecipa</a>
    </section>

  </div>
  

  <div class="service_details col-sm-8 col-md-8">
      <h2 class="section_header skincolored">
        <a href={$node.url_alias|ezurl()}>
          {$node.name|wash()|bracket_to_strong}
        </a>
      </h2>
      {if $node|has_attribute( 'call_to_action' )}
        <div class="alert alert-warning">
          {attribute_view_gui attribute=$node.object.data_map.call_to_action}
        </div>
      {/if}
      
    <section class="call_to_action hidden-lg hidden-md">
      <a href={$node.url_alias|ezurl()} class="btn btn-primary btn-lg btn-block">Partecipa</a>
    </section>
      
  </div>
</div>