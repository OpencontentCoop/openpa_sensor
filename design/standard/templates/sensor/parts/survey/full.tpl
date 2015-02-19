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
            {attribute_view_gui attribute=$node.data_map.survey}
        </div>
    </div>
</article>