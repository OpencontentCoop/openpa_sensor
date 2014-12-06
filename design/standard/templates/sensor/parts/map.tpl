<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.css" />
<link rel="stylesheet" href="{'stylesheets/MarkerCluster.css'|ezdesign(no)}" />
<link rel="stylesheet" href="{'stylesheets/MarkerCluster.Default.css'|ezdesign(no)}" />
<script src="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.js"></script>
<script src="{'javascript/Leaflet.MakiMarkers.js'|ezdesign(no)}"></script>
<script src="{'javascript/leaflet.markercluster.js'|ezdesign(no)}"></script>
{ezscript_require(array('ezjsc::jquery'))}

<div class="hidden-xs">
<div class="full_page_photo"><div id="map"></div></div>
</div>

{literal}
  <script type="text/javascript">	  		
		var tiles = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {maxZoom: 18,attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'});
		var map = L.map('map').addLayer(tiles);
    map.scrollWheelZoom.disable();
		var markers = L.markerClusterGroup();     
		$.getJSON("{/literal}{'/openpa/data/sensor'|ezurl(no)}{literal}?contentType=geojson", function(data) {      
      var geoJsonLayer = L.geoJson(data);
      markers.addLayer(geoJsonLayer);
      map.addLayer(markers);
      map.fitBounds(markers.getBounds());
    });    
    markers.on('click', function (a) {
      $.getJSON("{/literal}{'/openpa/data/sensor'|ezurl(no)}{literal}?contentType=marker&id="+a.layer.feature.id, function(data) {
        var popup = new L.Popup({maxHeight:360});
        popup.setLatLng(a.layer.getLatLng());
        popup.setContent(data.content);
        map.openPopup(popup); 
      });        
    });
  </script>
{/literal}