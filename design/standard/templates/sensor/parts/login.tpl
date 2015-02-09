{def $user = $current_user $method_name = '' $icon = false() $class = ''}
{def $is_user_anonymous = $user.contentobject_id|eq( ezini( 'UserSettings', 'AnonymousUserID' ) )}
{def $login_methods = ezini( 'ngconnect', 'LoginMethods', 'ngconnect.ini' )}
{def $login_window_type = ezini( 'ngconnect', 'LoginWindowType', 'ngconnect.ini' )|trim}

<section class="hgroup" id="login">
	<div class="row">
	  <div class="col-sm-12 col-md-12 text-center">
		<h1 style="margin-bottom: 1em">
		  {'Per partecipare devi iscriverti!'|i18n('openpa_sensor/signin')}      
		</h1>
	  </div>
	</div>  
    <div class="row">
        <div class="col-sm-6 col-md-6">
            {include uri='design:sensor/parts/signin.tpl'}
        </div>
        <div class="col-sm-6 col-md-6">
            {include uri='design:sensor/parts/signup.tpl'}
        </div>
    </div>
</section>