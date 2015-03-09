{def $post = object_handler($node).control_sensor}
<div class="row">
  <div class="col-md-12">
	<section class="hgroup">
	  <h2 class="section_header skincolored" style="margin-bottom: 0;border: none">
	  <a href={concat('sensor/posts/',$node.contentobject_id)|ezurl()}>
		{if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'config' ) )}
		<span class="label label-primary">{$node.object.id}</span>
		{/if}
		{$node.name|wash()}
	  </a>
	  <small>{$node.object.owner.name|wash()} {if $node.object|has_attribute('on_behalf_of')}[{$node.object|attribute('on_behalf_of').contentclass_attribute_name|wash()} {$node.object|attribute('on_behalf_of').content|wash()}]{/if}</small>
	  </h2>
	  <ul class="breadcrumb pull-right">
	  <li>
		<span class="label label-{$post.type.css_class}">{$post.type.name}</span>
		<span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
		{if $post.current_privacy_status.identifier|eq('private')}
		  <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
		{/if}
		{if $post.current_moderation_status.identifier|eq('waiting')}
		  <span class="label label-{$post.current_moderation_status.css_class}">{$post.current_moderation_status.name}</span>
		{/if}
		</li>
	  </ul>
	</section>
  </div>
</div>
<div class="row service_teaser" style="margin-bottom: 10px;">          
	{if $node|has_attribute('image')}
	<div class="service_photo col-sm-4 col-md-4">
	  <figure style="background-image:url({$node|attribute('image').content.large.full_path|ezroot(no)})"></figure>
	</div>
	{/if}
	<div class="service_details {if $node|has_attribute('image')}col-sm-8 col-md-8{else}col-sm-12 col-md-12{/if}">            
	  <div class="clearfix">
		  <p class="pull-left">
			  {if $node|has_attribute('geo')}
				  <i class="fa fa-map-marker"></i> {$node|attribute('geo').content.address}
			  {elseif $node|has_attribute('area')}
				  {attribute_view_gui attribute=$node|attribute('area')}
			  {/if}
		  </p>
		  {*<p class="pull-right">
			  <span class="label label-{$post.type.css_class}">{$post.type.name}</span>
			  <span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
			  {if $post.current_privacy_status.identifier|eq('private')}
				  <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
			  {/if}
		  </p>*}
	  </div>
	  <p>
		{attribute_view_gui attribute=$node|attribute('description')}
	  </p>
	  {if $node|has_attribute('attachment')}
		  <p>{attribute_view_gui attribute=$node|attribute('attachment')}</p>
	  {/if}
	  <ul class="list-inline">
		  <li><small><i class="fa fa-clock-o"></i> {'Pubblicata il'|i18n('openpa_sensor/post')} {$node.object.published|l10n(shortdatetime)}</small></li>
		  {if $node.object.modified|gt($node.object.published)}
			  <li><small><i class="fa fa-clock-o"></i> {'Ultima modifica del'|i18n('openpa_sensor/post')} {$node.object.modified|l10n(shortdatetime)}</small></li>
		  {/if}
		  {if $post.current_owner}<li><small><i class="fa fa-user"></i> In carico a {$post.current_owner}</small></li>{/if}
		  {if $post.comment_count|gt(0)}<li><small><i class="fa fa-comments"></i> {$post.comment_count} {'commenti'|i18n('openpa_sensor/post')}</small></li>{/if}
		  {if $post.response_count|gt(0)}<li><small><i class="fa fa-comment"></i> {$post.response_count} {'risposte ufficiali'|i18n('openpa_sensor/post')}</small></li>{/if}
		  {if $node.data_map.category.has_content}
			<li><small><i class="fa fa-tags"></i> {attribute_view_gui attribute=$node.data_map.category href=no-link}</small></li>
		  {/if}
	  </ul>
	  <a href={concat('sensor/posts/',$node.object.id)|ezurl()} class="btn btn-info btn-sm">{"Dettagli"|i18n('openpa_sensor/dashboard')}</a>
	  {if $node.object.can_edit}
		<a class="btn btn-warning btn-sm" href="{concat('sensor/edit/',$node.object.id)|ezurl(no)}"><i class="fa fa-edit"></i></a>
	  {/if}
	  {if $node.object.can_remove}
	  <form method="post" action={"content/action"|ezurl} style="display: inline">        
		  <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />                        
		  <input type="hidden" name="ContentNodeID" value="{$node.object.main_node_id}" />
		  <input type="hidden" name="RedirectURIAfterRemove" value="/sensor/dashboard" />
		  <input type="hidden" name="RedirectIfCancel" value="/sensor/dashboard" />                                
		  <button type="submit" class="btn btn-danger btn-sm" name="ActionRemove"><i class="fa fa-trash"></i></button>
	  </form>
	  {/if}

  </div>
</div>
{undef $post}