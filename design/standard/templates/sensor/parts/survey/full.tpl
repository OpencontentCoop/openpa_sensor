{if is_set($current_user)|not()}
    {def $current_user = fetch( 'user', 'current_user' )}
{/if}

<section class="hgroup">
    <h1>{$node.name|wash()}</h1>
</section>

<article class="post">
    <div class="post_content row">
        <div class="col-md-12">
            {if $node|has_attribute('image')}
                <figure>{attribute_view_gui attribute=$node.data_map.image image_class=original}</figure>
            {/if}
            {if $node|has_attribute('description')}
                {attribute_view_gui attribute=$node|attribute('description')}
            {/if}
            {if $current_user.is_logged_in}
                {attribute_view_gui attribute=$node.data_map.survey}
            {else}
                <a href="#login" class="btn btn-primary btn-lg btn-block">{'Accedi per rispondere'|i18n('openpa_sensor/menu')}</a>
            {/if}
        </div>
    </div>
</article>