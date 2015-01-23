<div class="row service_teaser">

  {if or( $total|gt(1), $more )}
  <div class="service_photo col-sm-4 col-md-4">
    {if $node|has_attribute('image')}
      <figure style="background-image:url({$node|attribute('image').content.large.full_path|ezroot(no)})"></figure>
    {/if}

    <section class="call_to_action">
      <a href={$node.url_alias|ezurl()} class="btn btn-primary btn-lg btn-block">Partecipa</a>
    </section>

  </div>
  {/if}

  <div class="service_details {if or( $total|gt(1), $more )}col-sm-8 col-md-8{else}col-sm-12 col-md-12{/if}">

    {if or( $total|gt(1), $more )}
      <h2 class="section_header skincolored">
        <a href={$node.url_alias|ezurl()}>
          {$node.name|wash()|bracket_to_strong}
        </a>
      </h2>
      {attribute_view_gui attribute=$node|attribute('description')}
    {else}
      <section class="hgroup">
          {attribute_view_gui attribute=$node|attribute('description')}
      </section>
    {/if}

  </div>
</div>