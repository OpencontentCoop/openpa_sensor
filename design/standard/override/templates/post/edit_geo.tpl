{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{if is_set( $attribute_base )|not}
    {def $attribute_base = 'ContentObjectAttribute'}
{/if}
{* Make sure to normalize floats from db  *}
{def $latitude  = $attribute.content.latitude|explode(',')|implode('.')
     $longitude = $attribute.content.longitude|explode(',')|implode('.')}


<div class="clearfix">
    <input class="form-control" size="20" type="text" id="input-address" name="{$attribute_base}_data_gmaplocation_address_{$attribute.id}" value="{$attribute.content.address}"/>
    <input class="btn-sm btn pull-left hidden-xs" type="button" id="mylocation-button" value="{'My current location'|i18n('extension/ezgmaplocation/datatype')}" title="{'Gets your current position if your browser support GeoLocation and you grant this website access to it! Most accurate if you have a built in gps in your Internet device! Also note that you might still have to type in address manually!'|i18n('extension/ezgmaplocation/datatype')}" />
	<input class="btn btn-sm pull-right hidden-xs" type="button" id="input-address-button" value="{'Find address'|i18n('extension/ezgmaplocation/datatype')}"/>    
</div>
<ul class="list-unstyled" id="input-results" style="max-height: 50px;overflow-y: auto;"></ul>

<input type="hidden" name="ezgml_hidden_address_{$attribute.id}" value="{$attribute.content.address}" disabled="disabled" />
<input type="hidden" name="ezgml_hidden_latitude_{$attribute.id}" value="{$latitude}" disabled="disabled" />
<input type="hidden" name="ezgml_hidden_longitude_{$attribute.id}" value="{$longitude}" disabled="disabled" />

<div class="row" style="display: none">
  <div class="col-xs-6">
    <input placeholder="{'Latitude'|i18n('extension/ezgmaplocation/datatype')}" id="latitude" class="form-control" type="text" name="{$attribute_base}_data_gmaplocation_latitude_{$attribute.id}" value="{$latitude}" />
  </div>
  <div class="col-xs-6">
    <input placeholder="{'Longitude'|i18n('extension/ezgmaplocation/datatype')}" id="longitude" class="form-control" type="text" name="{$attribute_base}_data_gmaplocation_longitude_{$attribute.id}" value="{$longitude}" />
  </div>
</div>


