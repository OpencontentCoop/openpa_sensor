{if $has_alerts}
<section class="top_bar animated slideInDown">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 tob_bar_right_col" style="text-align: left">
                <p>{foreach $alerts as $message}
                    {$message}{delimiter}<br />{/delimiter}
                {/foreach}
                </p>
            </div>
        </div>
    </div>
</section>
{/if}