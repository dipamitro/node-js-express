'use strict';

const sql = require('mysql');
const server = require('./server');

const mysql = sql.createConnection({
    host: server.mysql.host,
    database: server.mysql.database,
    user: server.mysql.user,
    password: server.mysql.password
});

mysql.connect(function(err) {
    if (err) throw err;
    console.log('database is connected successfully !');
});

module.exports = mysql;