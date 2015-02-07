{def $user_hash  = concat( $current_user.role_id_list|implode( ',' ), ',', $current_user.limited_assignment_value_list|implode( ',' ) )}
{cache-block ignore_content_expiry keys=array( $module_result.uri, $user_hash )}
{def $sensor = sensor_root_handler()}
<!doctype html>
<html class="no-js" lang="en">

{include uri='design:sensor/parts/head.tpl'}

<body>

<header>
    <div class="container">
        <div class="navbar navbar-default" role="navigation" style="position: relative; z-index: 10;">
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
            
{cache-block ignore_content_expiry keys=$current_user.contentobject_id}
    {include uri='design:sensor/parts/menu.tpl'}
{/cache-block}
            
        </div>
    </div>
</header>
<img alt="SensorCivico" src={"sensor_border.png"|ezimage()} style="position: absolute; top: 0; right: 0; border: 0;">

{if is_set( $sensor )|not()}{def $sensor = sensor_root_handler()}{/if}

{if is_set( $sensor_home )}
    {include uri='design:sensor/parts/home_image.tpl'}
{/if}

{if and( is_set( $module_result.node_id ), $module_result.node_id|eq( $sensor.post_container_node.node_id ) )}
  {include uri='design:sensor/parts/post/posts_map.tpl'}
{/if}

{if and( is_set( $module_result.node_id ), $module_result.node_id|eq( $sensor.forum_container_node.node_id ) )}  
  {include uri='design:sensor/parts/forum/root_slideshow.tpl'}  
{/if}

{*if and( is_set( $module_result.content_info.class_identifier ), $module_result.content_info.class_identifier|eq( 'dimmi_forum' ) )}  
  {include uri='design:sensor/parts/forum/forum_slideshow.tpl' node_id=$module_result.node_id}  
{/if*}


<div class="main">
    <div class="container">
        {$module_result.content}

        {if and( $current_user.is_logged_in|not(), is_set( $sensor_signup )|not )}
{cache-block ignore_content_expiry}            
          {include uri='design:sensor/parts/login.tpl'}
{/cache-block}            
        {/if}

    </div>

{cache-block expiry=86400 keys=array( $user_hash )}

  {if is_set( $sensor )|not()}{def $sensor = sensor_root_handler()}{/if}
  <footer>
      <section id="footer_teasers_wrapper">
          <div class="container">
              <div class="row">
                  <div class="footer_teaser col-sm-6 col-md-6">
                      <h3>{'Contatti'|i18n('openpa_sensor/menu')}</h3>
                      <p>{attribute_view_gui attribute=$sensor.contacts}</p>
                  </div>
                  <div class="footer_teaser col-sm-6 col-md-6">                      
                      <p>{attribute_view_gui attribute=$sensor.footer}</p>
                  </div>
              </div>
          </div>
      </section>
      <section class="copyright">
          <div class="container">
              <div class="row">
                  <div class="col-sm-12 col-md-12"> ©2014 Sensorcivico - progetto di riuso del Consorzio dei Comuni Trentini - realizzato da Opencontent con ComunWeb</div>
              </div>
          </div>
      </section>
  </footer>

</div>
{/cache-block}



<!--DEBUG_REPORT-->
</body>
</html>