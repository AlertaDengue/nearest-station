var express = require('express'),
    app = express(),
    port = process.env.PORT || 8000;

app.use(express.static('static'));
app.listen(port);
console.log('Listening on port', port);

