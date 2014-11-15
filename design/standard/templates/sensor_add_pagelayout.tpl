<!doctype html>
<html class="no-js" lang="en">
{def $sensor = sensor_root_handler()}
{include uri='design:sensor/parts/head.tpl'}
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.css" />
<!--[if lt IE 9]>
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.ie.css" />
<![endif]-->

<script src="http://cdn.leafletjs.com/leaflet-0.7.2/leaflet.js"></script>
<script src="http://techblog.mappy.com/Leaflet-active-area/src/leaflet.activearea.js"></script>
{ezscript_require(array('ezjsc::jquery', 'Leaflet.MakiMarkers.js','Control.Geocoder.js'))}

{literal}
<style>
    html,body{
        background: none;
        height: 100%;
    }
	@media (min-width:768px){
	  #edit .panel{
		overflow-y: auto;
		height: 100%;
		opacity: .9;
	  }
	  #edit{
		  position: fixed;
		  right: 30px;
		  top: 30px;		
		  z-index: 1000000;        
		  bottom: 30px;
	  }
	}
    @media (min-width:768px){
	  #sensor_full_map {
		  position: absolute;
		  top: 0;
		  bottom: 0;
		  width: 100%;
		  z-index: 1;
		  height: 100%;
	  }
	}
    .viewport {
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height:100%;
    }
	.edit-row{
	  margin: 10px 0;
	}
</style>
{/literal}
<body>


{$module_result.content}


<div id="sensor_full_map" class="hidden-xs"></div>

{literal}
    <script type="text/javascript">
	  if ( typeof CenterMap != 'undefined' ) {
		//var geocoder = new L.Control.Geocoder.Bing('Ahmnz1XxcrJXgiVWzx6W8ewWeqLGztZRIB1hysjaoHI5nV38WXxywjh6vj0lyl4u');
		var geocoder = new L.Control.Geocoder.Google('AIzaSyDVnxoH2lLysFsPPQcwxZ0ROYNVCBkmQZk');		  
		var map = new L.Map('sensor_full_map').setActiveArea('viewport').setView(CenterMap, 13);
		map.scrollWheelZoom.disable();

		var markers = L.featureGroup([L.marker(CenterMap)]);
		if ( typeof PointsOfInterest != 'undefined' ) {		  
		  $.each(PointsOfInterest, function (i,element) {			  
			  var customIcon = L.MakiMarkers.icon({icon: "heart", color: "#b0b", size: "m"});
			  markers.addLayer(L.marker(element.coords,{icon:customIcon}));
		  });
		}		
		markers.addTo(map);
		map.setView(CenterMap, 13);

		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);

		$('.poi').on( 'click', function(e){
		  var id = $(e.target).attr( 'id' );		  
		  $.each(PointsOfInterest, function (i,element) {
			  if (id == 'poi-'+element.id) {
				setContent(new L.latLng(element.coords[0],element.coords[1]));
				map.setView(element.coords, 13);				
			  }			  
		  });
		});		
		$('.zoomIn').on( 'click', function(e){
		  e.stopPropagation();
		  e.preventDefault();
		  map.setZoom(map.getZoom() < map.getMaxZoom() ? map.getZoom() + 1 : map.getMaxZoom());
		});
		
		$('.zoomOut').on( 'click', function(e){
		  e.stopPropagation();
		  e.preventDefault();
		  map.setZoom(map.getZoom() > map.getMinZoom() ? map.getZoom() - 1 : map.getMinZoom());
		});

		$('.fitbounds').on( 'click', function(e){
		  e.stopPropagation();
		  e.preventDefault();
		  map.fitBounds(markers.getBounds(), {padding: [10, 10]});
		});
		
		var userMarker;
		
		var setUserMarker = function(latlng) {
		  if( typeof( userMarker ) === 'undefined' ){
			var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
			userMarker = new L.marker(latlng,{icon:customIcon});
			userMarker.addTo(map);
			markers.addLayer(userMarker);
		  }else{
			userMarker.setLatLng(latlng);
		  }
		  setContent(latlng);		  
		}
		var setContent = function(latlng) {		  		  
		  $('input#latitude').val( latlng.lat );
		  $('input#longitude').val( latlng.lng );		  
		  console.log(latlng);
		  geocoder.reverse( latlng, 0, function(result) {
			$container = $('#input-results');
			$container.empty();
			$('input#input-address').val(result[0].name);
			//if (result.length > 1) {				
			//  $.each(result,function(i,o){				  
			//	var item = $('<li><span class="name hide">'+o.name+'</span>'+o.name+'</li>');				  
			//	item.on( 'click', function(e){
			//	  var name = $(e.target).find('.name').text();
			//	  $('input#input-address').val(name)
			//	});
			//	item.appendTo($container);
			//  });
			//}else{			  
			//  $('input#input-address').val(o.name);
			//}
		  }, this );	
		}
		
		map.on('click', function(e){		  
		  setUserMarker(e.latlng);
		});
		
		$('#input-address-button').on( 'click', function(e){
		  $query = $('#input-address').val();		  
		  console.log($query);		  
		  geocoder.geocode( $query, function(result) {			
			if (result.length > 0){
			  $container = $('#input-results');
			  $container.empty();
			  if (result.length > 1) {				
				$.each(result,function(i,o){				  
				  var item = $('<li><span class="latitude hide">'+o.center.lat+'</span><span class="longitude hide">'+o.center.lng+'</span>'+o.name+'</li>');				  
				  item.on( 'click', function(e){															
					var lat = $(e.target).find('.latitude').text();
					var lng = $(e.target).find('.longitude').text();					
					setUserMarker( new L.latLng(lat,lng) );
					map.setView([lat,lng], 17);
				  });
				  item.appendTo($container);
				});
			  }else{
				var latlng = new L.latLng(result[0].center.lat,result[0].center.lng);
				setUserMarker( latlng );
				map.setView(latlng, 17);
			  }
			}
		  }, this );
		});
		
		$('#mylocation-button').on( 'click', function(e){		  
		  map.locate({setView: true, watch: false})
		  .on('locationfound', function(e){			   
			  setUserMarker(new L.latLng(e.latitude, e.longitude));
		  })
		 .on('locationerror', function(e){
			  console.log(e);
			  alert("Location access denied.");
		  });
		});
	  }
    </script>
{/literal}


<!--DEBUG_REPORT-->
</body>
</html>