{cache-block ignore_content_expiry keys=array( $module_result.uri, $user_hash )}

{def $sensor = sensor_root_handler()}

<!doctype html>
<html class="no-js" lang="en">

{include uri='design:sensor/parts/head.tpl'}

<body>

{include uri='design:sensor/parts/header.tpl'}

{/cache-block}

{if is_set( $sensor_home )}
    {include uri='design:sensor/parts/home_image.tpl'}
{elseif is_set( $sensor_post )}
    {include uri='design:sensor/parts/post_map.tpl'}
{/if}

<div class="main">
    <div class="container">
        {$module_result.content}

        {if and( $current_user.is_logged_in|not(), is_current_sensor_page( 'signup' )|not )}
            {include uri='design:sensor/parts/login.tpl'}
        {/if}

    </div>

    {cache-block expiry=86400 keys=array( $user_hash )}
        {include uri='design:sensor/parts/footer.tpl'}

</div>

{include uri='design:sensor/parts/footer_script.tpl'}

{/cache-block}

<!--DEBUG_REPORT-->
</body>
</html>