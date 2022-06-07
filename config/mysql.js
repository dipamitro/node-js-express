'use strict';

const mysql = require('mysql');
const server = require('./server');

const conn = mysql.createConnection({
    host: server.mysql.host,
    database: server.mysql.database,
    user: server.mysql.user,
    password: server.mysql.password
});

conn.connect(function(err) {
    if (err) throw err;
    console.log('database is connected successfully !');
});

module.exports = conn;