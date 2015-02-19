<section class="hgroup">
    <h1>{$node.name|wash|bracket_to_strong}</h1>
</section>

<article class="post">
    <div class="post_content row">
        {*if $node|has_attribute( 'image' )}
            <div class="col-lg-12">
                <figure>{attribute_view_gui attribute=$node.data_map.image image_class=original}</figure>
            </div>
        {/if*}
        <div class="col-lg-12">
            {if $node|has_attribute( 'description' )}
                {attribute_view_gui attribute=$node.data_map.description}
            {/if}
        </div>
    </div>
</article>

<div id="partecipa">
    {include uri='design:sensor/parts/survey/survey_list.tpl'}
</div>