{cache-block ignore_content_expiry keys=array( $module_result.uri, $user_hash )}
<header>
    <div class="container">
        <div class="navbar navbar-default" role="navigation">
            <div class="navbar-header">
                <a class="navbar-brand" href="{'sensor/home'|ezurl(no)}">
                    <img src="{$sensor.logo|ezroot(no)}" alt="{$sensor.site_title}" height="90" width="90">
                    <span class="logo_title">{$sensor.logo_title}</span>
                    <span class="logo_subtitle">{$sensor.logo_subtitle}</span>
                </a>
                <a class="btn btn-navbar btn-default navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="nb_left pull-left">
                        <span class="fa fa-reorder"></span>
                    </span>
                    <span class="nb_right pull-right">Menu</span>
                </a>
            </div>
            {/cache-block}
            {include uri='design:sensor/parts/menu.tpl'}
            {cache-block ignore_content_expiry keys=array( $module_result.uri, $user_hash )}
        </div>
    </div>
</header>
{/cache-block}