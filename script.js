function centralize(bounds, width, height, xPos, yPos){
    var dx = Math.abs(bounds[1][0] - bounds[0][0]),
        dy = Math.abs(bounds[1][1] - bounds[0][1]),
        x = (bounds[0][0] + bounds[1][0]) / 2,
        y = (bounds[0][1] + bounds[1][1]) / 2,
        scale = 0.95 / Math.max(dx / width, dy / height),
        translate = [width * xPos - scale * x, height * yPos - scale * y];

    return {scale: scale, translate: translate};
}

function setupProjection(projection){
    projection.scale(1).translate([0, 0]);
    var brazilBounds = [[-74, 6], [-28, -34]].map(projection);
    var center = centralize(brazilBounds, width, height, 0.75, 0.5);
    projection.scale(center.scale).translate(center.translate);
    return projection;
}

function placeStations(stations, projection){
    stations = stations.map(function(d){
        var lat = parseFloat(d.Latitude);
        d.Latitude = lat > 6? lat * -1: lat;
        d.Longitude = parseFloat(d.Longitude);
        return d;
    });

    this.selectAll('circle')
      .data(stations)
      .enter()
      .append('circle')
      .attr("transform", function(d) { return "translate(" + projection([d.Longitude, d.Latitude]) + ")"; })
      .attr("r", 2)
      .append('title')
      .text(function(d){ return d['Estação']; });
}

function genericError(err){
    console.error(err);
}

function nearestStation(distance, point){
    return function (a, b){
        return distance(point, a) - distance(point, b);
    }
}
