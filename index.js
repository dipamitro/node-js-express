'use strict';

const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');
const ip = require('ip');
const server = require('./config/server');
const web = require('./routes/web');
const auth = require('./routes/auth');

const app = express();

app.use(express.json());
app.use(cors());
app.use(bodyParser.json());
app.use(express.urlencoded({
    extended: true
}));

app.use('/public', express.static(path.join(__dirname, 'public')));

app.use('/api', web.routes);
app.use('/api', auth.routes);

app.listen(server.port, () => {
    console.log(`Example app listening on url http://` + ip.address() + `:` + server.port)
});
