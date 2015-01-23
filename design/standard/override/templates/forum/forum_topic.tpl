{def $page_limit=20
     $reply_limit=cond( $view_parameters.offset|gt( 0 ), 20, 19 )
     $reply_offset=cond( $view_parameters.offset|gt( 0 ), sub( $view_parameters.offset, 1 ), $view_parameters.offset )
     $reply_count=fetch('content','tree_count', hash( parent_node_id, $node.node_id ) )}

<section class="hgroup">
  <h1>{$node.name|wash|bracket_to_strong}</h1>
  <h2>
    <i class="fa fa-clock-o"></i> {$node.object.published|datetime( 'custom', '%l, %d %F %Y' )}
    {if $reply_count|gt(0)}<a href="#post_comments"><i class="fa fa-comments-o"></i> {$reply_count}  {if $reply_count|gt(1)}commenti{else}commento{/if}</a>{/if}
  </h2>
  <ul class="breadcrumb pull-right">
    <li><a href="{$node.parent.url_alias|ezurl(no)}"><small>{$node.parent.name|wash()|bracket_to_strong}</small></a></li>
  </ul>
</section>

<article class="post">
  <div class="post_content row">

    <div class="col-lg-12">
      <figure>{attribute_view_gui attribute=$node.data_map.image image_class=original}</figure>
    </div>

    <div class="col-lg-{if $node.data_map.approfondimenti.has_content}9{else}12{/if}">
      <p>{$node.data_map.message.content|simpletags|wordtoimage|autolink|bracket_to_strong}</p>
    </div>

    {if $node.data_map.approfondimenti.has_content}
      <div class="alert alert-info col-lg-3">
        <strong>Per saperne di più...</strong>
        <ul class="list list-unstyled">
          {foreach $node.data_map.approfondimenti.content.rows.sequential as $s}
            <li><a href="{$s.columns[1]}">{$s.columns[0]}</a></li>
          {/foreach}
        </ul>
      </div>
    {/if}

  </div>

  <div class="row">
    <div class="col-md-6 text-center">
      <h4><span class="label label-info">Come valuti la chiarezza di questa proposta?</span></h4>
      {include uri='design:sensor/parts/forum/rating.tpl' attribute=$node.data_map.star_rating}
    </div>
    <div class="col-md-6 people_rating text-center">
      <h4><span class="label label-info">Come valuti l'importanza di questa proposta?</span></h4>
      {include uri='design:sensor/parts/forum/rating.tpl' attribute=$node.data_map.usefull_rating}
    </div>
  </div>

</article>

{if $reply_count}
{def $replies = fetch('content','list', hash( 'parent_node_id', $node.node_id, 'limit', $reply_limit, 'offset', $reply_offset, 'sort_by', array( array( published, true() ) ) ) )}

<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div id="post_comments">
      <h4>Commenti</h4>
      <div class="comment">
      {foreach $replies as $reply}
        {include name=forum_reply uri='design:sensor/parts/forum/reply.tpl' reply=$reply recursion=0}
      {/foreach}
      </div>
    </div>
  </div>
</div>
{/if}







{if $node.object.can_create}
  {def $notification_access=fetch( 'user', 'has_access_to', hash( 'module', 'notification', 'function', 'use' ) )}
  <form method="post" action={"content/action/"|ezurl}>
    <input class="btn btn-lg btn-primary" type="submit" name="NewButton" value="{'Inserisci commento'|i18n( 'design/ocbootstrap/full/forum_topic' )}" />
    <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
    <input type="hidden" name="ContentObjectID" value="{$node.contentobject_id}" />
    {if $notification_access}
    <input class="btn btn-lg btn-info pull-right" type="submit" name="ActionAddToNotification" value="{'Keep me updated'|i18n( 'design/ocbootstrap/full/forum_topic' )}" />
    {/if}
    <input type="hidden" name="NodeID" value="{$node.node_id}" />
    <input type="hidden" name="ClassIdentifier" value="dimmi_forum_reply" />
    <input type="hidden" name="ContentLanguageCode" value="{ezini( 'RegionalSettings', 'ContentObjectLocale', 'site.ini')}" />
  </form>
{else}
    <div class="message msg-error">
        <span>{"You need to be logged in to get access to the forums. You can do so %login_link_start%here%login_link_end%"|i18n( "design/ocbootstrap/full/forum_topic",, hash( '%login_link_start%', concat( '<a href=', '/user/login/'|ezurl, '>' ), '%login_link_end%', '</a>' ) )}</span>
    </div>
{/if}



{include name=navigator
        uri='design:navigator/google.tpl'
        page_uri=$node.url_alias
        item_count=$reply_count
        view_parameters=$view_parameters
        item_limit=$page_limit}

