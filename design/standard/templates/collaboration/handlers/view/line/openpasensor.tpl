{def $content_object=fetch("content","object",hash("object_id",$item.content.content_object_id))
     $openpa_object = object_handler( $content_object )}


{if $content_object}

    {$item.data_int3} - {$content_object.name|wash()}

{else}

  <p>La segnalazione {$item.content.content_object_id} non &egrave; accessibile o &egrave; stata rimossa.</p>

{/if}