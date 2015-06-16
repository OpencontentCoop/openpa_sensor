<!doctype html>
<html class="no-js" lang="en">
{def $sensor = sensor_root_handler()}
{include uri='design:sensor/parts/head.tpl'}
{ezcss_require(array(
    'leaflet.0.7.2.css',
    'Control.Loading.css'
))}
{ezscript_require(array(
    'leaflet.0.7.2.js',
    'ezjsc::jquery',
    'leaflet.activearea.js',
    'Leaflet.MakiMarkers.js',
    'Control.Geocoder.js',
    'Control.Loading.js'
))}

{literal}
    <style>
        html, body {
            background: none;
            height: 100%;
            width: 100%;
        }

        .zindexize {
            z-index: 10 !important;
        }

        .edit-row {
            margin: 10px 0;
        }

        .panel {
            margin: 0;
        }

        @media (min-width: 320px) {
            #sensor_full_map {
                z-index: -1;
                position: absolute;
                top: 0;
                bottom: 0;
                width: 100%;
                height: 100%;
                bottom: 0;
            }

            #sensor_hide_map_button {
                position: absolute;
                right: 50px;
                top: 10px;
                z-index: -1;
            }

            #mylocation-mobile-button {
                position: absolute;
                right: 10px;
                top: 10px;
                z-index: -1;
            }

            #edit {
                padding: 0;
                right: 0;
                bottom: 0;
                margin: 0;
            }

            .viewport {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }

            form.edit .tab-pane {
                padding: 0;
            }
            .alert {
                left: 0;
                position: absolute;
                top: 0;
                width: 100%;
            }
        }

        @media (min-width: 768px) {
            #edit .panel {
                overflow-y: auto;
                height: 100%;
                opacity: .9;
            }

            #edit {
                position: fixed;
                right: 10px;
                z-index: 1000000;
                bottom: 10px;
            }

            #sensor_full_map {
                z-index: 1;
            }

            #sensor_hide_map_button,
            #sensor_show_map_button {
                display: none;
            }

            .viewport {
                position: absolute;
                top: 0;
                left: 0;
                width: 40%;
                height: 100%;
            }

            form.edit .tab-pane {
                padding: 20px;
            }
            .alert {
                left: auto;
                position: static;
                top: auto;
                width: auto;
            }
        }
    </style>
{/literal}
<body>

{$module_result.content}

{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $(document).on('click', '#sensor_show_map_button', function () {
                $(window).scrollTop(0);
                $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').addClass('zindexize');
            });
            $(document).on('click', '#sensor_hide_map_button', function () {
                $('#sensor_hide_map_button, #sensor_full_map, #mylocation-mobile-button').removeClass('zindexize');
            });
        });
        if (PointsOfInterest.length > 0) {
            var CenterMap = new L.latLng(PointsOfInterest[0].coords[0], PointsOfInterest[0].coords[1]);
            var control = new L.Control.Geocoder({geocoder: null});

            if (window.XDomainRequest) {
                control.options.geocoder = L.Control.Geocoder.bing('Ahmnz1XxcrJXgiVWzx6W8ewWeqLGztZRIB1hysjaoHI5nV38WXxywjh6vj0lyl4u');
            }
            else {
                control.options.geocoder = L.Control.Geocoder.google('AIzaSyDVnxoH2lLysFsPPQcwxZ0ROYNVCBkmQZk');
            }

            var map = new L.Map('sensor_full_map', {loadingControl: true}).setActiveArea('viewport');
            map.scrollWheelZoom.disable();

            var alreadyAddressButton = false;
            var markers = L.featureGroup();
            var userMarker;
            var setUserMarker = function (latlng, name) {
                if (typeof( userMarker ) === 'undefined') {
                    var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
                    userMarker = new L.marker(latlng, {icon: customIcon, draggable: true});
                    userMarker.on('dragend', function (event) {
                        var marker = event.target;
                        var position = marker.getLatLng();
                        marker.setLatLng(position);
                        setContent(position);
                    });
                    userMarker.addTo(map);
                    markers.addLayer(userMarker);
                } else {
                    userMarker.setLatLng(latlng);
                }
                setContent(latlng, name);
                alreadyAddressButton = false;
                var $container = $('#input-results');
                $container.empty();
            };

            var setContent = function (latlng, name) {
                $('input#latitude').val(latlng.lat);
                $('input#longitude').val(latlng.lng);
                if (typeof name == 'undefined') {
                    var name = latlng.toString();
                    map.loadingControl.addLoader('sc');
                    control.options.geocoder.reverse(latlng, 0, function (result) {
                        if (result.length > 0) name = result[0].name;
                        $container = $('#input-results');
                        $container.empty();
                        $('input#input-address').val(name);
                        map.setView(latlng, 17);
                        userMarker.bindPopup(name).openPopup();
                        map.loadingControl.removeLoader('sc');
                    }, this);
                } else {
                    $('input#input-address').val(name);
                    map.setView(latlng, 17);
                    userMarker.bindPopup(name).openPopup();
                }
            };
            setUserMarker(CenterMap);
            /*if ( typeof PointsOfInterest != 'undefined' ) {
             $.each(PointsOfInterest, function (i,element) {
             var customIcon = L.MakiMarkers.icon({icon: "heart", color: "#b0b", size: "m"});
             markers.addLayer(L.marker(element.coords,{icon:customIcon}));
             });
             }
             markers.addTo(map);
             map.setView(CenterMap, 13);*/

            L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            $('#poi').on('change', function (e) {
                var id = $('#poi option:selected').val();
                $.each(PointsOfInterest, function (i, element) {
                    if (id == element.id) {
                        var latLng = new L.latLng(element.coords[0], element.coords[1]);
                        setUserMarker(latLng);
                    }
                });
            });

            $('.zoomIn').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                map.setZoom(map.getZoom() < map.getMaxZoom() ? map.getZoom() + 1 : map.getMaxZoom());
            });

            $('.zoomOut').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                map.setZoom(map.getZoom() > map.getMinZoom() ? map.getZoom() - 1 : map.getMinZoom());
            });

            $('.fitbounds').on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                map.fitBounds(markers.getBounds(), {padding: [10, 10]});
            });

            map.on('click', function (e) {
                setUserMarker(e.latlng);
            });

            $('#input-address')
                    .on('click', function (e) {
                        $(this).select();
                    })
                    .on('keypress', function (e) {
                        if (e.which == 13) {
                            $('#input-address-button').trigger('click');
                            e.preventDefault();
                        }
                    })
                    .on('focusout', function (e) {
                        if (!alreadyAddressButton)
                            $('#input-address-button').trigger('click');
                    });

            $('#input-address-button')
                    .on('click', function (e) {
                        alreadyAddressButton = true;
                        map.loadingControl.addLoader('gc');
                        $query = $('#input-address').val();
                        control.options.geocoder.geocode($query, function (result) {
                            if (result.length > 0) {
                                $container = $('#input-results');
                                $container.empty();
                                if (result.length > 1) {
                                    $.each(result, function (i, o) {
                                        var item = $('<li style="cursor:pointer"><span class="latitude hide">' + o.center.lat + '</span><span class="longitude hide">' + o.center.lng + '</span><span class="name">' + o.name + '</span></li>');
                                        item.appendTo($container);
                                        item.on('click', function (e) {
                                            var lat = $(e.target).parents('li').find('.latitude').text();
                                            var lng = $(e.target).parents('li').find('.longitude').text();
                                            var name = $(e.target).parents('li').find('.name').text();
                                            map.loadingControl.removeLoader('gc');
                                            setUserMarker(new L.latLng(lat, lng), name);
                                        });
                                    });
                                } else {
                                    var latlng = new L.latLng(result[0].center.lat, result[0].center.lng);
                                    map.loadingControl.removeLoader('gc');
                                    setUserMarker(latlng, result[0].name);
                                }
                            }
                        }, this);
                        map.loadingControl.removeLoader('gc');
                    });

            $('#mylocation-button, #mylocation-mobile-button')
                    .on('click', function (e) {
                        var icon = $(e.currentTarget).find('i');
                        icon.addClass('fa-spin');
                        map.loadingControl.addLoader('lc');
                        map.locate({setView: true, watch: false})
                            .on('locationfound', function (e) {
                                map.loadingControl.removeLoader('lc');
                                icon.removeClass('fa-spin');
                                setUserMarker(new L.latLng(e.latitude, e.longitude));
                            })
                            .on('locationerror', function (e) {
                                icon.removeClass('fa-spin');
                                map.loadingControl.removeLoader('lc');
                                alert(e.message);
                            });
                    });
        }
    </script>
{/literal}


<!--DEBUG_REPORT-->
</body>
</html>
