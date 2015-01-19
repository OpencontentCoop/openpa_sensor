{def $dimensions = ''}
{if is_set( $height )}
  {set $dimensions = concat( $dimensions, 'height="', $height, '" ' )}
{/if}
{if is_set( $width )}
  {set $dimensions = concat( $dimensions, 'width="', $width, '" ' )}
{/if}

{if and( is_set( $object.data_map.image ), $object.data_map.image.has_content )}
    <img src={"user_placeholder.jpg"|ezimage()} class="img-circle" {$dimensions} />
{else}
    <img src={"user_placeholder.jpg"|ezimage()} class="img-circle" {$dimensions} />
{/if}