{if $post_geo_array_js}
<link rel="stylesheet" href="{'stylesheets/leaflet.css'|ezdesign(no)}" />

<script src="{'javascript/leaflet.js'|ezdesign(no)}"></script>
<script src="{'javascript/leaflet.activearea.js'|ezdesign(no)}"></script>
<script src="{'javascript/Leaflet.MakiMarkers.js'|ezdesign(no)}"></script>

<div id="map" style="width: 100%; height: 200px;"></div>

{if $object|has_attribute('geo')}
	<small><i class="fa fa-map-marker"></i> {$object|attribute('geo').content.address}</small>
{elseif $object|has_attribute('area')}
	<small><i class="fa fa-map-marker"></i> {attribute_view_gui attribute=$object|attribute('area')}</small>
{/if}


{literal}
    <script type="text/javascript">
        var latlng={/literal}{$post_geo_array_js}{literal};
        var map = new L.Map('map');
        map.scrollWheelZoom.disable();
        var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
        var postMarker = new L.marker(latlng,{icon:customIcon});
        postMarker.addTo(map);
        map.setView(latlng, 18);
        L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    </script>
{/literal}
{/if}
