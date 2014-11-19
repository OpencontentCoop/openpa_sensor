{cache-block ignore_content_expiry keys=array( $identifier )}
{def $sensor = sensor_root_handler()}
{if is_set( $sensor[$identifier] )}

<section class="hgroup">
  <h1>
    {$sensor[$identifier].contentclass_attribute_name|wash()}
  </h1>    
</section>

<div class="row">
  <div class="col-md-12">
    {attribute_view_gui attribute=$sensor[$identifier]}
  </div>
</div>

{/if}
{/cache-block}