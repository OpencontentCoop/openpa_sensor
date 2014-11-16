{if $post_geo_array_js}
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.css" />
<!--[if lt IE 9]>
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.ie.css" />
<![endif]-->

<script src="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.js"></script>
<script src="http://techblog.mappy.com/Leaflet-active-area/src/leaflet.activearea.js"></script>
<script src="{'javascript/Leaflet.MakiMarkers.js'|ezdesign(no)}"></script>

<div class="full_page_photo"><div id="map"></div></div>

{literal}
    <script type="text/javascript">
        var latlng={/literal}{$post_geo_array_js}{literal};
        var map = new L.Map('map');
        map.scrollWheelZoom.disable();
        var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
        var postMarker = new L.marker(latlng,{icon:customIcon});
        postMarker.addTo(map);
        map.setView(latlng, 17);
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    </script>
{/literal}
{/if}