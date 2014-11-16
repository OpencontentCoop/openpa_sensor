{cache-block keys=array( $current_user.contentobject_id )}
<div class="collapse navbar-collapse">
    <ul class="nav pull-right navbar-nav">
        <li class="active"><a href="{'sensor/home'|ezurl(no)}">{'SensorCivico'|i18n('openpa_sensor/menu')}</a></li>
        {*<li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">Segnalazioni<span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="#">Segnalazioni</a></li>
                <li><a href="#">Aggiornamenti</a></li>
            </ul>
        </li>*}
        <li><a href="{'sensor/posts'|ezurl(no)}">{'Segnalazioni'|i18n('openpa_sensor/menu')}</a></li>
        <li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="{'sensor/info'|ezurl(no)}">{'Info'|i18n('openpa_sensor/menu')}
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
                <li><a href="{'sensor/info/faq'|ezurl(no)}">{'Faq'|i18n('openpa_sensor/menu')}</a></li>
                <li><a href="{'sensor/info/privacy'|ezurl(no)}">{'Privacy'|i18n('openpa_sensor/menu')}</a></li>
                <li><a href="{'sensor/info/terms'|ezurl(no)}">{'Termini di utilizzo'|i18n('openpa_sensor/menu')}</a></li>
            </ul>
        </li>
        {if $current_user.is_logged_in|not()}
            <li>
                <a href="#login">
                                <span class="label label-primary" style="font-size: 100%">
                                    {'Accedi'|i18n('openpa_sensor/menu')}
                                </span>
                </a>
            </li>
        {else}
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="{'user/edit'|ezurl(no)}">
                    <span style="text-transform: none"><small>{$current_user.contentobject.name|wash()}<br />{$current_user.email|shorten(40)|wash()}</small></span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{'user/edit'|ezurl(no)}">{'Profilo'|i18n('openpa_sensor/menu')}</a></li>
                    <li><a href="{'notification/settings'|ezurl(no)}">{'Notifiche'|i18n('openpa_sensor/menu')}</a></li>
                    <li><a href="{'user/logout'|ezurl(no)}">{'Esci'|i18n('openpa_sensor/menu')}</a></li>
                </ul>
            </li>
            <li>
                <a href="{'sensor/add'|ezurl(no)}">
                                <span class="label label-primary" style="font-size: 100%">
                                    {'Segnala'|i18n('openpa_sensor/menu')}
                                </span>
                </a>
            </li>
        {/if}
        <li><img height="50px" src={"logo_sensor.png"|ezimage()}></li>
    </ul>
</div>
{/cache-block}