{def $content_object=fetch("content","object",hash("object_id",$item.content.content_object_id))
     $openpa_object = object_handler( $content_object )
     $post = $item.content.helper.sensor}

{if $content_object}

    <span class="label label-{$post.type.css_class}">{$post.type.name}</span>
    <span class="label label-{$post.current_status.css_class}">{$post.current_status.name}</span>
    {if $post.current_privacy_status.identifier|eq('private')}
      <span class="label label-{$post.current_privacy_status.css_class}">{$post.current_privacy_status.name}</span>
    {/if}
    
    {$content_object.name|wash()}

{else}

  <p>La segnalazione {$item.content.content_object_id} non &egrave; accessibile o &egrave; stata rimossa.</p>

{/if}