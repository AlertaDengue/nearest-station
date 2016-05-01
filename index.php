<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Citimus</title>
    <script src="https://d3js.org/d3.v3.js" charset="utf-8"></script>
    <script src="script.js" charset="utf-8"></script>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,300italic,400italic,600italic,700italic,800italic,800' rel='stylesheet' type='text/css'>
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
<div id="toolbox">
    <a class="export" href="" download="estações-mais-proximas.csv">Baixar csv.</a>
</div>
<table cellspacing="0">
    <thead>
        <tr>
            <th>Município</th>
            <th>Estação</th>
            <th>ICAO</th>
            <th>WMO</th>
            <th>Distância</th>
        </tr>
    </thead>
    <tbody></tbody>
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
          this.current.classed("active", false);
          this.current = d3.select(el).classed("active", true);
      },
  };

function createCsv(data){
    function joinFields(row){ return row.join(',');}
    var dataProtocol = "data:text/csv;charset=utf-8,";
    var rows = data.map(joinFields).join('\n');
    return dataProtocol + rows;
};

function updateCsvLink(csvContent){
    var encodedUri = encodeURI(csvContent);
    var link = document.querySelector("a");
    link.setAttribute("href", encodedUri);
}

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
      stations = stations.map(function(station){
            station.long = parseFloat(station.Longitude);
            station.lat = parseFloat(station.Latitude);
            if(station.lat > 6) station.lat *= -1;
            return station;
      });
      placeStations.call(gStations, stations, projection);
      window.stations = stations;
  }).on('error', genericError).get();


  function featureCenter(feature, width, height){
      var bounds = path.bounds(feature);
      return centralize(bounds, width, height, 0.75, 0.5);
  }

  function focusState(gParent, center){
      gParent.transition()
          .duration(750)
          .attr("transform", "translate(" + center.translate + ")scale(" + center.scale + ")");

      gStations.selectAll('circle').attr('r', 2/center.scale);
  }

  function clicked(gMap, path, width, height) {
      return function(d){
          if(active.current.empty()){
              var header = document.querySelector('body > header');
              header.style.minHeight =  '0';
          }
          active.set(this);

          var center = featureCenter(d, width, height);
          focusState(gMap, center);

          d3.select('#place-name').text(d.properties.name + '.')

          var state = d.properties.sigla.toLowerCase();
          loadMunicipalities(state);
      };
  }

  function distance(point, station){
    return d3.geo.distance(point, [station.long, station.lat]);
  }

  function rad2km(rad){
      var earthRadius = 6371;
      return rad * earthRadius;
  }

  function computeNearest(d){
      var centroid = d3.geo.centroid(d);
      var station = stations.sort(nearestStation(distance, centroid))[0];
      return [
          d.properties.NM_MUNICIP,
          station['Estação'],
          station['ICAO'],
          station['WMO'],
          d3.round(rad2km(distance(centroid, station)), 2),
      ];
  }

  function showNearest(data){
      var tbody = d3.select('table tbody')
      var trs = tbody.selectAll('tr')
          .data(data, function(d){ return d[0];});
      var tr = trs.enter().append('tr')
      tr.append('td').text(function(d){ return d[0];});
      tr.append('td').text(function(d){ return d[1];});
      tr.append('td').text(function(d){ return d[2];});
      tr.append('td').text(function(d){ return d[3];});
      tr.append('td').text(function(d){ return d[4];});
      trs.exit().transition().duration(500).style('opacity', 0).remove();
  }

  function placeMunicipalities(container, features){
      function title(d){ return d.properties.NM_MUNICIP;}
      container.selectAll('path')
          .data(features)
          .enter()
          .append('path')
          .attr('d', path)
          .append('title')
          .text(title);
  }

  function loadMunicipalities(state, cb){
    var urlMunicipalities = baseUrl + state + '-municipalities.json'
    d3.json(urlMunicipalities).on('load', function(geojson){
        gMunicipalities.call(placeMunicipalities, geojson.features);
        var nearestStations = geojson.features.map(computeNearest);
        showNearest(nearestStations);
        updateCsvLink(createCsv(nearestStations));

        if(cb && cb.call) cb();
    }).on('beforesend', function(){
        console.log('Loading map.');
    }).on('error', genericError).get();
  }

</script>
</body>
</html>
