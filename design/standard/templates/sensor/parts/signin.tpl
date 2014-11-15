<div class="signin">
    <div class="social_sign">

        <h3>{'Accedi con il tuo account social'|i18n('openpa_sensor')}</h3>

        {if $login_methods|count}
            {foreach $login_methods as $l}
                {if or( $is_user_anonymous, and( $is_user_anonymous|not, fetch( ngconnect, user_has_connection, hash( user_id, $user.contentobject_id, login_method, $l ) )|not ) )}
                    {switch match=$l}
                    {case match='facebook'}
                    {set $icon = "fa fa-facebook" $class = "fb"}
                    {/case}
                    {case match='twitter'}
                    {set $icon = "fa fa-twitter" $class = "tw"}
                    {/case}
                    {case match='google'}
                    {set $icon = "fa fa-google-plus" $class = "gp"}
                    {/case}
                    {case}{/case}
                    {/switch}
                    {if $icon}
                        {set $method_name = ezini( concat( 'LoginMethod_', $l ), 'MethodName', 'ngconnect.ini' )}
                        {if $login_window_type|eq( 'popup' )}
                            <a class="{$class}" href="#" onclick="window.open( '{concat( 'layout/set/ngconnect/ngconnect/login/', $l, '?redirectURI=', cond( and( is_set( $module_result.node_id ), is_set( $module_result.content_info.url_alias ) ), $module_result.content_info.url_alias, '' )|urlencode )|ezurl( no, full )}', '', 'resizable=1,scrollbars=1,width=800,height=420' );return false;">
                                <i class="{$icon}"></i>
                            </a>
                        {else}
                            <a class="{$class}" href={concat( 'ngconnect/login/', $l, '?redirectURI=', cond( and( is_set( $module_result.node_id ), is_set( $module_result.content_info.url_alias ) ), $module_result.content_info.url_alias, true(), '' )|urlencode )|ezurl}>
                                <i class="{$icon}"></i>
                            </a>
                        {/if}
                    {/if}
                    {set $icon = false()}
                {/if}
            {/foreach}
        {/if}
    </div>
    <div class="or">
        <div class="or_l"></div>
        <span>{'oppure'|i18n('openpa_sensor')}</span>
        <div class="or_r"></div>
    </div>
    <p class="sign_title">{'Accedi con il tuo account SensorCivico'|i18n('openpa_sensor')}</p>
    <div class="row">
        <div class="col-lg-2"></div>
        <div class="form col-lg-8">
            <form name="loginform" method="post" action={'/user/login/'|ezurl}>
                <input placeholder="{'Indirizzo Email'|i18n('openpa_sensor')}" class="form-control" type="text" name="Login">
                <input placeholder="{'Password'|i18n('openpa_sensor')}" class="form-control" type="text" name="Password">
                <div class="forgot">
                    <div class="checkbox">
                        <label class="">
                            <input type="checkbox" name="Cookie"> {'Resta collegato'|i18n('openpa_sensor')}
                        </label>
                    </div>
                    <a href={'/user/forgotpassword'|ezurl}>{'Hai dimenticato la password?'|i18n('openpa_sensor')}</a>
                </div>
                <button name="LoginButton" type="submit" class="btn btn-primary btn-lg">{'Accedi'|i18n('openpa_sensor')}</button>
            </form>
        </div>
        <div class="col-lg-2"></div>
    </div>
</div>