{def $sensor_post = $node.object|sensor_post()}
<div class="row">
  <div class="col-md-12">
	<section class="hgroup">
	  <h2 class="section_header skincolored" style="margin-bottom: 0;border: none">
	  <a href={concat('sensor/posts/',$sensor_post.id)|ezurl()}>
		<span class="label label-primary">{$sensor_post.id}</span>
		{$sensor_post.object.name|wash()}
	  </a>
	  <small>{$sensor_post.object.owner.name|wash()} {if $sensor_post.object|has_attribute('on_behalf_of')}[{$sensor_post.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$sensor_post.object|attribute('on_behalf_of').content|wash()}]{/if}</small>
	  </h2>
	  <ul class="breadcrumb pull-right">
	  <li>
		<span class="label label-{$sensor_post.type.css_class}">{$sensor_post.type.name}</span>
		<span class="label label-{$sensor_post.current_object_state.css_class}">{$sensor_post.current_object_state.name}</span>
		{if $sensor_post.current_privacy_state.identifier|eq('private')}
		  <span class="label label-{$sensor_post.current_privacy_state.css_class}">{$sensor_post.current_privacy_state.name}</span>
		{/if}
		{if $sensor_post.current_moderation_state.identifier|eq('waiting')}
		  <span class="label label-{$sensor_post.current_moderation_state.css_class}">{$sensor_post.current_moderation_state.name}</span>
		{/if}
		</li>
	  </ul>
	</section>
  </div>
</div>
<div class="row service_teaser" style="margin-bottom: 10px;">          
	{if $sensor_post.object|has_attribute('image')}
	<div class="service_photo col-sm-4 col-md-4">
	  <figure style="background-image:url({$sensor_post.object|attribute('image').content.large.full_path|ezroot(no)})"></figure>
	</div>
	{/if}
	<div class="service_details {if $sensor_post.object|has_attribute('image')}col-sm-8 col-md-8{else}col-sm-12 col-md-12{/if}">
	  <div class="clearfix">
		  <p class="pull-left">
			  {if $sensor_post.object|has_attribute('geo')}
				  <i class="fa fa-map-marker"></i> {$sensor_post.object|attribute('geo').content.address}
			  {elseif $sensor_post.object|has_attribute('area')}
				  {attribute_view_gui attribute=$sensor_post.object|attribute('area')}
			  {/if}
		  </p>
	  </div>
	  <p>
		{attribute_view_gui attribute=$sensor_post.object|attribute('description')}
	  </p>
	  {if $sensor_post.object|has_attribute('attachment')}
		  <p>{attribute_view_gui attribute=$sensor_post.object|attribute('attachment')}</p>
	  {/if}
	  <ul class="list-inline">
		  <li><small><i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('openpa_sensor/post')} {$sensor_post.object.published|l10n(shortdatetime)}</small></li>
		  {if $sensor_post.object.modified|gt($sensor_post.object.published)}
			  <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('openpa_sensor/post')} {$sensor_post.object.modified|l10n(shortdatetime)}</small></li>
		  {/if}
		  {if $sensor_post.current_owner}<li><small><i class="fa fa-user"></i> In carico a {$sensor_post.current_owner}</small></li>{/if}
		  {if $sensor_post.comment_count|gt(0)}<li><small><i class="fa fa-comments"></i> {$sensor_post.comment_count} {'commenti'|i18n('openpa_sensor/post')}</small></li>{/if}
		  {if $sensor_post.response_count|gt(0)}<li><small><i class="fa fa-comment"></i> {$sensor_post.response_count} {'risposte ufficiali'|i18n('openpa_sensor/post')}</small></li>{/if}
		  {if $node.data_map.category.has_content}
			<li><small><i class="fa fa-tags"></i> {attribute_view_gui attribute=$node.data_map.category href=no-link}</small></li>
		  {/if}
	  </ul>
	  <a href={concat('sensor/posts/',$sensor_post.object.id)|ezurl()} class="btn btn-info btn-sm">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a>
	  {if $sensor_post.object.can_edit}
		<a class="btn btn-warning btn-sm" href="{concat('sensor/edit/',$sensor_post.object.id)|ezurl(no)}"><i class="fa fa-edit"></i></a>
	  {/if}
	  {if $sensor_post.object.can_remove}
	  <form method="post" action={"content/action"|ezurl} style="display: inline">        
		  <input type="hidden" name="ContentObjectID" value="{$sensor_post.object.id}" />
		  <input type="hidden" name="ContentNodeID" value="{$sensor_post.object.main_node_id}" />
		  <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard" />
		  <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard" />                                
		  <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove"><i class="fa fa-trash"></i></button>
	  </form>
	  {/if}

  </div>
</div>
{undef $sensor_post}