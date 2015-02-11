{def $sensor = sensor_root_handler()
     $areas = $sensor.areas}

<script type="text/javascript">
  var PointsOfInterest = {$sensor.areas.coords_json};
</script>  


<form id="edit" class="post-edit edit col-md-6 col-xs-12" enctype="multipart/form-data" method="post" action={concat("/content/edit/",$object.id,"/",$edit_version,"/",$edit_language|not|choose(concat($edit_language,"/"),''))|ezurl}>

  <div class="panel panel-default">
	<div class="panel-body">
	  
	  <div class="navbar hidden-sm hidden-md hidden-lg" style="margin-top: 0">
		  <a class="navbar-brand" href="{'sensor/home'|ezurl(no)}">
			  <img src="{$sensor.logo|ezroot(no)}" alt="{$sensor.site_title}" height="90" width="90">
			  <span class="logo_title">{$sensor.logo_title}</span>
			  <span class="logo_subtitle">{$sensor.logo_subtitle}</span>
		  </a>
	  </div>

	{if ezini_hasvariable( 'EditSettings', 'AdditionalTemplates', 'content.ini' )}
	  {foreach ezini( 'EditSettings', 'AdditionalTemplates', 'content.ini' ) as $additional_tpl}
		{include uri=concat( 'design:', $additional_tpl )}
	  {/foreach}
	{/if}

	{default $view_parameters            = array()
			 $attribute_categorys        = ezini( 'ClassAttributeSettings', 'CategoryList', 'content.ini' )
			 $attribute_default_category = ezini( 'ClassAttributeSettings', 'DefaultCategory', 'content.ini' )}

	{def $count = 0}
	{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
	  {if $attribute_group|ne('hidden')}
		{set $count = $count|inc()}
	  {/if}
	{/foreach}

	{if $count|gt(1)}
	  {set $count = 0}
	  <ul class="nav nav-tabs">
		{set $count = 0}
		{foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
		  {if $attribute_group|ne('hidden')}
			<li class="{if $count|eq(0)} active{/if}">
			  <a data-toggle="tab" href="#attribute-group-{$attribute_group}">{$attribute_categorys[$attribute_group]}</a>
			</li>
			{set $count = $count|inc()}
		  {/if}
		{/foreach}
	  </ul>
	{/if}

  {def $content_attributes_extra = hash()}
  {if fetch( 'user', 'has_access_to', hash( 'module', 'sensor', 'function', 'behalf' ) )}
    {def $behalf = $content_attributes_grouped_data_map['hidden']['on_behalf_of']}    
    {set $content_attributes_extra = hash( 'on_behalf_of', $behalf )}
  {/if}
  
	<div class="tab-content">
	  {set $count = 0}
	  {foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
		
    {if $attribute_group|eq('hidden')}{skip}{/if}
    
    {if $attribute_group|eq('content')}{set $content_attributes_grouped = $content_attributes_grouped|merge($content_attributes_extra)}{/if}
    
    <div class="clearfix attribute-edit tab-pane{if $count|eq(0)} active{/if}" id="attribute-group-{$attribute_group}">
			{set $count = $count|inc()}
			{foreach $content_attributes_grouped as $attribute_identifier => $attribute}
				{def $contentclass_attribute = $attribute.contentclass_attribute}				
        <div class="row edit-row ezcca-edit-datatype-{$attribute.data_type_string} ezcca-edit-{$attribute_identifier}">
					
					{if and( eq( $attribute.can_translate, 0 ), ne( $object.initial_language_code, $attribute.language_code ) )}
						<div class="col-md-3">
							<label>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
							{if $attribute.can_translate|not} <span class="nontranslatable">({'not translatable'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:
							{if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
						</label>
						</div>
						<div class="col-md-8">
							{if $is_translating_content}
								<div class="original">
									{attribute_view_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
									<input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
								</div>
							{else}
								{attribute_view_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
								<input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
							{/if}
						</div>
					{else}
						{if $is_translating_content}
							<div class="col-md-3">
								<label{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
								{if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
								{if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:
								{if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
							</label>
							</div>
							<div class="col-md-8">
								<div class="original">
								{attribute_view_gui attribute_base=$attribute_base attribute=$from_content_attributes_grouped_data_map[$attribute_group][$attribute_identifier] view_parameters=$view_parameters}
							</div>
								<div class="translation">
								{if $attribute.display_info.edit.grouped_input}
									<fieldset>
										{attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
										<input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
									</fieldset>
								{else}
									{attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
									<input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
								{/if}
							</div>
							</div>
						{else}
							{if $attribute.display_info.edit.grouped_input}
								<div class="col-md-3">
									<p{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
										{if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}
										{if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}
									</p>
								</div>
								<div class="col-md-9">
									{if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
									{attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
									<input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
								</div>
							{else}
								<div class="col-md-3">
									{if $contentclass_attribute.data_type_string|ne('ezboolean')}
                                    <p{if $attribute.has_validation_error} class="message-error"{/if}>
                                        <span style="white-space: nowrap">{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}{if $attribute.is_required} <span class="required" title="{'required'|i18n( 'design/admin/content/edit_attribute' )}">*</span>{/if}</span>
                                        {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}
									</p>
                                    {/if}
								</div>
								<div class="col-md-9">
									{if $contentclass_attribute.description} <span class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</span>{/if}
									{attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters html_class='form-control'}
									<input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
								</div>
							{/if}
						{/if}
					{/if}
				</div>
				{undef $contentclass_attribute}
			{/foreach}
		</div>
	  {/foreach}
	</div>

	{section show=$validation.processed}
		{section show=or( $validation.attributes, $validation.placement, $validation.custom_rules )}
			<div class="alert alert-warning alert-dismissible" role="alert"> 
			  <button type="button" class="close" data-dismiss="alert">
			  <span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				{section show=$validation.attributes}
					<p>{'Required data is either missing or is invalid'|i18n( 'design/admin/content/edit' )}:</p>
					<ul class="list-unstyled">
						{section var=UnvalidatedAttributes loop=$validation.attributes}
							<li><strong>{$UnvalidatedAttributes.item.name|wash}:</strong> {$UnvalidatedAttributes.item.description}</li>
						{/section}
					</ul>
				{/section}
	
				{section show=$validation.placement}
					<p>{'The following locations are invalid'|i18n( 'design/admin/content/edit' )}:</p>
					<ul class="list-unstyled">
						{section var=UnvalidatedPlacements loop=$validation.placement}
							<li>{$UnvalidatedPlacements.item.text}</li>
						{/section}
					</ul>
				{/section}
	
				{section show=$validation.custom_rules}
					<p>{'The following data is invalid according to the custom validation rules'|i18n( 'design/admin/content/edit' )}:</p>
					<ul class="list-unstyled">
						{section var=UnvalidatedCustomRules loop=$validation.custom_rules}
							<li>{$UnvalidatedCustomRules.item.text}</li>
						{/section}
					</ul>
				{/section}
			</div>
	
		{section-else}
	
			{section show=$validation_log}
				<div class="alert alert-warning alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					{section var=ValidationLogs loop=$validation_log}
						<p>{$ValidationLogs.item.name|wash}:</p>
						<ul>
							{section var=LogMessages loop=$ValidationLogs.item.description}
								<li>{$LogMessages.item}</li>
							{/section}
						</ul>
					{/section}
				</div>            
			{/section}
		{/section}
	{/section}

	  <p class="text-center">
		<small>		  
		  {'I testi e le immagini inserite dovranno rispettare le policy stabilite per la <a target="_blank" href="%privacy_link">privacy</a>'|i18n('openpa_sensor/add',,hash('%privacy_link', '/sensor/redirect/info:privacy'|ezurl(no,full) ) )}
		</small>
	  </p>
	
	  <div class="buttonblock">
		<input class="btn btn-lg btn-success pull-right" type="submit" name="PublishButton" value="Salva" />
		<input class="btn btn-lg btn-danger" type="submit" name="DiscardButton" value="{'Discard'|i18n('design/standard/content/edit')}" />
		<input type="hidden" name="DiscardConfirm" value="0" />
		<input type="hidden" name="RedirectIfDiscarded" value="/sensor/redirect/home" />
		<input type="hidden" name="RedirectURIAfterPublish" value="/sensor/redirect/posts,{$object.id}" />
	  </div>
  </div>
</div>

</form>