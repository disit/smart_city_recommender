<?php

/* Recommender
  Copyright (C) 2017 DISIT Lab http://www.disit.org - University of Florence

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>. */
ini_set('max_execution_time', 9999999); //300 seconds = 5 minutes
ini_set("memory_limit", "-1");

// calculate distance in km between coordinates in decimal degrees (latitude, longitude)
function distFrom($lat1, $lng1, $lat2, $lng2) {
    if (($lat2 == 0 && $lng2 == 0) || ($lat1 == $lat2 && $lon1 == $lon2))
        return 0;
    $earthRadius = 6371000; //meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $dist = $earthRadius * $c;

    return $dist / 1000;
}

function getFilename() {
    $geojson = "";
    if (isset($_REQUEST["profile"]))
        $geojson .= "_" . $_REQUEST["profile"];
    if (isset($_REQUEST["hour"]))
        $geojson .= "_" . $_REQUEST["hour"];
    if (isset($_REQUEST["cluster"]))
        $geojson .= "_" . $_REQUEST["cluster"];
    return $geojson;
}

$profile = isset($_REQUEST["profile"]) ? "_" . $_REQUEST["profile"] : "";
$hour = isset($_REQUEST["hour"]) ? "_" . $_REQUEST["hour"] : "";
$cluster = isset($_REQUEST["cluster"]) ? "_" . $_REQUEST["cluster"] : "";
$radius = isset($_REQUEST["radius"]) ? "_" . $_REQUEST["radius"] : "";

// if file exists don't calculate flows
/* if (file_exists("./adj" . $profile . $hour . $cluster . ".csv") && filesize("./adj" . $profile . $hour . $cluster . ".csv") > 0) {
  $file = file_get_contents("./adj" . $profile . $hour . $cluster . ".csv");
  echo $file;
  exit();
  } */

if (file_exists("../links" . $profile . $hour . $cluster . ".csv")) {
    $csv = array_map('str_getcsv', file("../links" . $profile . $hour . $cluster . ".csv"));
} else {
    $csv = array_map('str_getcsv', file("../permutations/links" . $profile . $hour . $cluster . ".csv"));
}

// parse geoJSON file and map clusters' ids to coordinates
$clusters = array();
$geojson = file_get_contents("../permutations/nodes" . $profile . $hour . $cluster . ".geojson");
$geojson = json_decode($geojson, true);
foreach ($geojson["features"] as $k => $v) {
    $clusters[$v["id"]]["lat"] = doubleval($v["properties"]["LAT"]);
    $clusters[$v["id"]]["lon"] = doubleval($v["properties"]["LON"]);
}

$index = 0;
$counter = 0;
$areas['nodes'] = array();

// if radius in km within Florence is set, then calculate only flows in that range (top 100)
if (isset($_REQUEST["radius"])) {
    for ($i = 1; $i < count($csv); $i++) {
        if ($counter == 100) {
            break;
        }
        $lat_lon_target = $clusters[$csv[$i][0]];
        $lat_lon_source = $clusters[$csv[$i][1]];

        if (distFrom($lat_lon_target["lat"], $lat_lon_target["lon"], 43.76990127563477, 11.25531959533691) <= $_REQUEST["radius"] ||
                distFrom($lat_lon_source["lat"], $lat_lon_source["lon"], 43.76990127563477, 11.25531959533691) <= $_REQUEST["radius"]) {
            $nodes['name'] = $csv[$i][0];
            $nodes['group'] = $csv[$i][0];
            if (!in_array($nodes, $areas['nodes'])) {
                $areas['nodes'][] = $nodes;
            }

            $nodes['name'] = $csv[$i][1];
            $nodes['group'] = $csv[$i][1];
            if (!in_array($nodes, $areas['nodes'])) {
                $areas['nodes'][] = $nodes;
            }

            if (!isset($zip[$csv[$i][0]])) {
                $zip[$csv[$i][0]] = $index;
                $index++;
            }

            if (!isset($zip[$csv[$i][1]])) {
                $zip[$csv[$i][1]] = $index;
                $index++;
            }

            $links['source'] = $zip[$csv[$i][1]];
            $links['target'] = $zip[$csv[$i][0]];
            $links['value'] = intval($csv[$i][2]);

            $areas["links"][] = $links;

            $counter++;
        }
    }
}
// top 100 flows
else {
    for ($i = 1; $i < 100; $i++) {
        $nodes['name'] = $csv[$i][0];
        $nodes['group'] = $csv[$i][0];
        if (!in_array($nodes, $areas['nodes'])) {
            $areas['nodes'][] = $nodes;
        }

        $nodes['name'] = $csv[$i][1];
        $nodes['group'] = $csv[$i][1];
        if (!in_array($nodes, $areas['nodes'])) {
            $areas['nodes'][] = $nodes;
        }

        if (!isset($zip[$csv[$i][0]])) {
            $zip[$csv[$i][0]] = $index;
            $index++;
        }

        if (!isset($zip[$csv[$i][1]])) {
            $zip[$csv[$i][1]] = $index;
            $index++;
        }

        $links['source'] = $zip[$csv[$i][1]];
        $links['target'] = $zip[$csv[$i][0]];
        $links['value'] = intval($csv[$i][2]);

        $areas["links"][] = $links;
    }
}

if (isset($areas)) {
    //file_put_contents("./adj" . $profile . $hour . $cluster . ".csv", json_encode($areas));
    echo json_encode($areas);
} else {
    echo "error";
}
?>

