function centralize(bounds, width, height){
    var dx = Math.abs(bounds[1][0] - bounds[0][0]),
        dy = Math.abs(bounds[1][1] - bounds[0][1]),
        x = (bounds[0][0] + bounds[1][0]) / 2,
        y = (bounds[0][1] + bounds[1][1]) / 2,
        scale = 0.95 / Math.max(dx / width, dy / height),
        translate = [width / 2 - scale * x, height / 2 - scale * y];

    return {scale: scale, translate: translate};
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

function palaceLocations(features, path){
    var sel = this.selectAll('path').data(features);
    sel.enter().append('path');
    sel.exit().remove();
    return this.selectAll('path').attr('d', path);
}

function genericError(err){
    console.error(err);
}
