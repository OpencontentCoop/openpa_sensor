{def $owner = $reply.object.owner}
<div class="row">

  <figure class="col-sm-1 col-md-1 col-md-offset-{$recursion}">
    {include uri='design:sensor/parts/user_image.tpl' object=$owner}
  </figure>

  <div class="col-sm-{10|sub($recursion)} col-md-{10|sub($recursion)}">

    <div class="comment_name">
      {$owner.name|wash}


      <div class="pull-right">
        {if and( $recursion|eq(0), $reply.object.can_create )}
          <a href="{concat('openpa/add/',$reply.class_identifier, '/?parent=',$reply.node_id)|ezurl(no)}" class="reply btn btn-xs btn-primary">Rispondi</a>
        {/if}
        {include name=edit uri='design:parts/toolbar/node_edit.tpl' current_node=$reply}
        {include name=trash uri='design:parts/toolbar/node_trash.tpl' current_node=$reply}
      </div>

      {foreach $reply.object.author_array as $author}
        {if ne( $reply.object.owner_id, $author.contentobject_id )}
          {'Moderated by'|i18n( 'design/ocbootstrap/full/forum_topic' )}: {$author.contentobject.name|wash}
        {/if}
      {/foreach}

    </div>

    <div class="comment_date">
      <i class="fa fa-clock-o"></i> {$reply.object.published|datetime( 'custom', '%l, %d %F %Y %H:%i' )} {if $reply.object.current_version|gt(1)}<em> <i class="fa fa-pencil"></i> Modificato</em>{/if}
    </div>

    <div class="the_comment">
      <p>{$reply.object.data_map.message.content|simpletags|wordtoimage|autolink}</p>
    </div>

    {if $reply.object.data_map.links.has_content}
      <div class="reply-attachments">
        <strong>Links utili</strong>
        {def $links = $reply.object.data_map.links.content|explode(',')}
        <ul class="list-unstyled">
        {foreach $links as $l}
          <li>{$l|autolink}</li>
        {/foreach}
        </ul>
      </div>
    {/if}

    {if $reply.object.data_map.attachments.has_content}
      <div class="reply-attachments">
        <strong>Allegati</strong>
        <p>{attribute_view_gui attribute=$reply.data_map.attachments}</p>
      </div>
    {/if}

  </div>

  <div class="col-sm-1 col-md-1">
    {include uri='design:sensor/parts/forum/rating.tpl' attribute=$reply.object.data_map.like_rating}
  </div>


</div>
{undef $owner}

{if and( $recursion|eq(0), $reply.children_count|gt(0) )}
  {foreach $reply.children as $child}
    {include name=forum_reply uri='design:sensor/parts/forum/reply.tpl' reply=$child recursion=1}
  {/foreach}
{/if}