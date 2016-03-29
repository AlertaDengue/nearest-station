<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Citimus</title>
    <script src="https://d3js.org/d3.v3.js" charset="utf-8"></script>
    <script src="script.js" charset="utf-8"></script>
    <link href="style.css" rel="stylesheet" />
</head>
<body>
<h1>Estações meteorológicas do Brasil.</h1>
<svg>
  <g class="map">
    <g class="states"></g>
    <g class="municipalities"></g>
    <g class="stations"></g>
  </g>
</svg>
<script type="text/javascript">
  var urlStations = 'https://raw.githubusercontent.com/AlertaDengue/AlertaDengueCaptura/master/utilities/stations/stations_seed.csv';

  var width = document.body.clientWidth,
      height = document.body.clientHeight;

  var svg = d3.select('svg')
              .attr('viewBox', '0 0 ' + width + ' ' + height);

  var projection = d3.geo.mercator().scale(1).translate([0, 0]);
  var brazilBounds = [[-74, 6], [-28, -34]].map(projection); //path.bounds(geojson),
  var center = centralize(brazilBounds, width, height);
  projection.scale(center.scale).translate(center.translate);

  var path = d3.geo.path().projection(projection);

  var gMap = svg.select('g.map'),
      gStations = gMap.select('g.stations'),
      gStates = gMap.select('g.states'),
      gMunicipalities = gMap.select('g.municipalities');

  d3.json('/brazil_geo.json', function(err, geojson){
      if(err){ console.log(err);}
      palaceLocations.call(gStates, geojson.features, path)
          .append('title')
          .text(function(d){ return d.properties.name; });

      gStates.selectAll('path').on('click', clicked(gMap, path, width, height));
  });

  d3.csv(urlStations, function (err, stations){
      if(err){console.log(err)}
      console.log(stations.filter(d => parseFloat(d.Latitude) > 6));
      placeStations.call(gStations, stations, projection);
      window.stations = stations;
  });


  function clicked(g, path, width, height) {
      var active = d3.select(null);
      return function(d){
          active.classed("active", false);
          active = d3.select(this).classed("active", true);

          var bounds = path.bounds(d);
          var center = centralize(bounds, width, height);

          g.transition()
              .duration(750)
              .attr("transform", "translate(" + center.translate + ")scale(" + center.scale + ")");

          gStations.selectAll('circle').attr('r', 2/center.scale);
          console.log(d.properties)
          loadMunicipalities(d.properties.sigla.toLowerCase());
      };
  }

  function nearestStation(feature, stations){
      var centroid = d3.geo.centroid(feature);
      function distance(station){
        var long = parseFloat(station.Longitude);
        var lat = parseFloat(station.Latitude);
        return d3.geo.distance(centroid, [long, lat]);
      }
      var distances = stations.sort(function(a, b){
          return distance(a) - distance(b);
      });
      return distances;
  }

  function loadMunicipalities(state){
    var urlMunicipalities= state + '-municipalities.json';
    d3.json(urlMunicipalities, function(err, geojson){
        if(err){console.log(err)}
        var paths = palaceLocations.call(gMunicipalities, geojson.features, path);
        paths.each(function(d){
          var station = nearestStation(d, stations)[0];
          console.log(d.properties.NM_MUNICIP, ':', station['Estação'], ':', station['ICAO'], ':', station['WMO'])
        });
        paths.on('click', function(d){
            nearestStation(d, stations)
        })
        paths
          .append('title')
          .text(function(d){ return d.properties.NM_MUNICIP;});
    });
  }

</script>
</body>
</html>
