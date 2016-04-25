<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Citimus</title>
    <script src="https://d3js.org/d3.v3.js" charset="utf-8"></script>
    <script src="script.js" charset="utf-8"></script>
    <link href="style.css" rel="stylesheet" />
</head>
<body>
<header>
    <div>
        <h1>
            <div><span>Estações meteorológicas:</span></div>
            <div><span id="place-name">Brasil.</span></div>
        </h1>
    </div>
    <svg>
      <g class="map">
        <g class="states"></g>
        <g class="municipalities"></g>
        <g class="stations"></g>
      </g>
    </svg>
</header>
<section>
<table cellspacing="0">
    <thead>
        <tr>
            <th>Município</th>
            <th>Estação</th>
            <th>ICAO</th>
            <th>WMO</th>
        </tr>
    </thead>
</table>
</section>
<script type="text/javascript">
  var baseUrl = 'http://sandbox.israelst.com/br-atlas/geo/';
  var urlStations = 'https://raw.githubusercontent.com/AlertaDengue/AlertaDengueCaptura/master/utilities/stations/stations_seed.csv';

  var width = document.body.clientWidth,
      height = document.body.clientHeight;

  var svg = d3.select('svg')
              .attr('viewBox', '0 0 ' + width + ' ' + height);


  var projection = setupProjection(d3.geo.mercator());
  var path = d3.geo.path().projection(projection);

  var gMap = svg.select('g.map'),
      gStations = gMap.select('g.stations'),
      gStates = gMap.select('g.states'),
      gMunicipalities = gMap.select('g.municipalities');

  var active = {
      current: d3.select(null),
      set: function (el){
          if(this.current.empty()){
              var header = document.querySelector('body > header');
              header.style.minHeight =  '0';
          }else{
              this.current.classed("active", false);
          }
          this.current = el.classed("active", true);
      },
  };

  d3.json(baseUrl + 'brazil_simplified.json').on('load', function (geojson){
      function title(d){ return d.properties.name; }
      gStates.selectAll('path')
            .data(geojson.features)
            .enter()
            .append('path')
            .attr('d', path)
            .on('click', clicked(gMap, path, width, height))
            .append('title')
            .text(title);
  }).on('error', genericError).get();

  d3.csv(urlStations).on('load', function (stations){
      console.log(stations.filter(d => parseFloat(d.Latitude) > 6));
      placeStations.call(gStations, stations, projection);
      window.stations = stations;
  }).on('error', genericError).get();


  function clicked(g, path, width, height) {
      return function(d){
          active.set(d3.select(this));
          var bounds = path.bounds(d);
          var center = centralize(bounds, width, height, 0.75, 0.5);

          g.transition()
              .duration(750)
              .attr("transform", "translate(" + center.translate + ")scale(" + center.scale + ")");

          gStations.selectAll('circle').attr('r', 2/center.scale);
          d3.select('#place-name').text(d.properties.name + '.')
          loadMunicipalities(d.properties.sigla.toLowerCase());
      };
  }

  function distance(point, station){
    var long = parseFloat(station.Longitude);
    var lat = parseFloat(station.Latitude);
    return d3.geo.distance(point, [long, lat]);
  }

  function showNearest(d){
      var centroid = d3.geo.centroid(d);
      var station = stations.sort(nearestStation(distance, centroid))[0];
      var row = d3.select('table').append('tr');
      row.append('td').text(d.properties.NM_MUNICIP)
      row.append('td').text(station['Estação'])
      row.append('td').text(station['ICAO'])
      row.append('td').text(station['WMO'])
  }

  function loadMunicipalities(state){
    var urlMunicipalities = baseUrl + state + '-municipalities.json'
    d3.json(urlMunicipalities).on('load', function(geojson){
        function title(d){ return d.properties.NM_MUNICIP;}
        gMunicipalities.selectAll('path')
            .data(geojson.features)
            .enter()
            .append('path')
            .attr('d', path)
            .each(showNearest)
            .append('title')
            .text(title);
    }).on('error', genericError).get();
  }

</script>
</body>
</html>
