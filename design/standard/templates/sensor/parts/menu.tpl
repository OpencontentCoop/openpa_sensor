<div class="collapse navbar-collapse">


    <ul class="nav pull-right navbar-nav">
        {*<li class="active"><a href="{'sensor/home'|ezurl(no)}">{'SensorCivico'|i18n('openpa_sensor/menu')}</a></li>*}
        {*<li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">Segnalazioni<span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="#">Segnalazioni</a></li>
                <li><a href="#">Aggiornamenti</a></li>
            </ul>
        </li>*}
        
        {def $avail_translation = language_switcher( $site.uri.original_uri)}
        {if $avail_translation|gt(1)}
        {foreach $avail_translation as $siteaccess => $lang}
          <li>
            <a href={$lang.url|ezurl}>
              {if $siteaccess|eq($access_type.name)}
                <span class="label label-default" style="font-size: 100%">{$lang.text|wash}</span>
              {else}
                {$lang.text|wash}
              {/if}
            </a>
          </li>
        {/foreach}
        {/if}
        
        {if $current_user.is_logged_in|not()}
        <li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="{'sensor/info'|ezurl(no)}">{'Informazioni'|i18n('openpa_sensor/menu')}
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
                <li><a href="{'sensor/info/faq'|ezurl(no)}">{'Faq'|i18n('openpa_sensor/menu')}</a></li>
                <li><a href="{'sensor/info/privacy'|ezurl(no)}">{'Privacy'|i18n('openpa_sensor/menu')}</a></li>
                <li><a href="{'sensor/info/terms'|ezurl(no)}">{'Termini di utilizzo'|i18n('openpa_sensor/menu')}</a></li>
            </ul>
        </li>		
            <li><a href="{'sensor/posts'|ezurl(no)}">{'Segnalazioni'|i18n('openpa_sensor/menu')}</a></li>
			<li>
                <a href="#login">
                                <span class="label label-primary" style="font-size: 100%">
                                    {'Accedi'|i18n('openpa_sensor/menu')}
                                </span>
                </a>
            </li>			
        {else}			
			<li><a href="{'sensor/posts'|ezurl(no)}">{'Tutte le segnalazioni'|i18n('openpa_sensor/menu')}</a></li>
			<li><a href="{'sensor/dashboard'|ezurl(no)}">{'Le mie segnalazioni'|i18n('openpa_sensor/menu')}</a></li>
            <li>
                <a href="{'sensor/add'|ezurl(no)}">
				  <span class="label label-primary" style="font-size: 100%">
					  {'Segnala'|i18n('openpa_sensor/menu')}
				  </span>
                </a>
            </li>
          <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    {include uri='design:sensor/parts/user_image.tpl' object=$current_user.contentobject height=25 width=25}
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
					<li>
					  <span style="text-transform: none;padding: 3px 20px;display: block;background: #eee;"><small>{$current_user.contentobject.name|wash()}<br />{$current_user.email|shorten(40)|wash()}</small></span>
					</li>
                    <li><a href="{'user/edit'|ezurl(no)}">{'Profilo'|i18n('openpa_sensor/menu')}</a></li>
                    <li><a href="{'notification/settings'|ezurl(no)}">{'Notifiche'|i18n('openpa_sensor/menu')}</a></li>
					{if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'config' ) )}
					  <li><a href="{'sensor/config'|ezurl(no)}">{'Settings'|i18n('openpa_sensor/menu')}</a></li>
					{/if}
                    <li><a href="{'user/logout'|ezurl(no)}">{'Esci'|i18n('openpa_sensor/menu')}</a></li>
                </ul>
            </li>
        {/if}
        {*<li><img height="50px" src={"logo_sensor.png"|ezimage()}></li>*}
    </ul>
</div>