<?php
        $lat  = 59.445751;
        $lng  = 17.849338;
        $city = '';

        $map_response = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='. $lat.','.$lng.'&sensor=false&result_type=country&key=AIzaSyBqqhqN5q545cx57GD5ht6JVidUQuuGd34');

        $geo_address = json_decode($map_response , true);

        echo ($geo_address['results'][0]['address_components'][0]['long_name']);

        }