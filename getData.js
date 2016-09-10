var fs = require('fs'),
    https = require('https'),
    path = require('path'),
    zlib = require('zlib'),
    csvPath = path.join('static', 'data'),
    csvFile = path.join(csvPath, 'stations_seed.csv'),
    gzFile = csvFile + '.gz',
    headers = {'Accept-Encoding': 'gzip'};


if (!fs.existsSync(csvPath)){
    fs.mkdirSync(csvPath);
}

if(fs.existsSync(gzFile)){
    headers['If-Modified-Since'] = fs.statSync(gzFile).mtime.toGMTString();
}

console.log('Gathering data...');
https.get({
    host: 'raw.githubusercontent.com',
    path: '/AlertaDengue/AlertaDengueCaptura/master/utilities/stations/stations_seed.csv',
    rejectUnauthorized: false,
    headers: headers
}).on('response', function(response){
    console.info('Status code: ', response.statusCode);
    if(response.statusCode === 200){
        console.log('Receiving dozens of csv lines, please, be patient. :)');
        response.pipe(fs.createWriteStream(gzFile));
        response.pipe(zlib.createGunzip())
                .pipe(fs.createWriteStream(csvFile));
    }else{
        response.resume();
    }
    response.on('end', function(){
        // The modified time of the file and last-modified header don't match
        var mtime = new Date(this.headers['last-modified']);
        fs.utimesSync(gzFile, mtime, mtime);
        console.log('Done.');
    });
}).on('error', function(error){
    console.error("Unreachable data:", error.code);
});

