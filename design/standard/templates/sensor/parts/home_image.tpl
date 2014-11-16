{if is_set( $sensor )|not()}
    {def $sensor = sensor_root_handler()}
{/if}
{if $sensor.banner}
<div class="full_page_photo" style='background-image: url({$sensor.banner|ezroot()});'>
    <div class="container">
        <section class="call_to_action">
            <h3 class="animated bounceInDown">{$sensor.banner_title}</h3>
            <h4 class="animated bounceInUp skincolored">{$sensor.banner_subtitle}</h4>
        </section>
    </div>
</div>
{/if}