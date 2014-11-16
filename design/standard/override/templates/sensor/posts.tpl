{def $page_limit = 10
    $posts = fetch( content, list, hash( parent_node_id, sensor_root_handler().post_container_node.node_id,
    limit, $page_limit,
    offset, $view_parameters.offset,
    sort_by, array( 'published', false()))
)}
<section class="service_teasers">
    {foreach $posts as $item}
    {def $post = object_handler($item).control_sensor}
    <div class="service_teaser">
        <div class="row">
            <div class="service_details col-sm-12 col-md-12">
                <h2 class="section_header skincolored">
                    <a href={concat('sensor/posts/',$item.contentobject_id)|ezurl()}>{$item.name|wash()}</a>
                    <small>{$item.object.owner.name|wash()}</small>
                </h2>
                <div class="clearfix">
                    <p class="pull-left">
                        {if $item|has_attribute('geo')}
                            <i class="fa fa-map-marker"></i> {$item|attribute('geo').content.address}
                        {elseif $item|has_attribute('area')}
                            {attribute_view_gui attribute=$item|attribute('area')}
                        {/if}
                    </p>
                    <p class="pull-right">
                        <span class="label label-{$post.type.css_class}">{$post.type.name}</span>
                        <span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
                        {if $post.current_privacy_status.identifier|eq('private')}
                            <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
                        {/if}
                    </p>
                </div>
                <p>
                    {attribute_view_gui attribute=$item|attribute('description')}
                </p>
                {if $item|has_attribute('attachment')}
                    <p>{attribute_view_gui attribute=$item|attribute('attachment')}</p>
                {/if}
                <ul class="list-inline">
                    <li><small><i class="fa fa-clock"></i> Pubblicata il {$item.object.published|l10n(shortdate)}</small></li>
                    {if $item.object.modified|gt($item.object.published)}
                        <li><small><i class="fa fa-clock-o"></i> Ultima modifica del {$item.object.modified|l10n(shortdate)}</small></li>
                    {/if}
                    <li><small><i class="fa fa-user"></i> In carico a {$post.current_owner}</small></li>
                    <li><small><i class="fa fa-comment"></i> {$post.comment_count} commenti</small></li>
                </ul>
            </div>
        </div>
    </div>
    {undef $post}
    {/foreach}

{include name=navigator
    uri='design:navigator/google.tpl'
    page_uri=$node.url_alias
    item_count=sensor_root_handler().post_container_node.children_count
    view_parameters=$view_parameters
    item_limit=$page_limit}

</section>