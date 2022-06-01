'use strict';

const dotenv = require('dotenv');
const assert = require('assert');

dotenv.config();

const { PORT, HOST, HOST_URL, MONGODB_HOST, MONGODB_DATABASE, MYSQL_HOST , MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD } = process.env;

assert(PORT, 'PORT is required');
assert(HOST, 'HOST is required');

module.exports = {
    port: PORT,
    host: HOST,
    url: HOST_URL,
    mongodb: {
        host: MONGODB_HOST,
        database: MONGODB_DATABASE
    },
    mysql: {
        host: MYSQL_HOST,
        database: MYSQL_DATABASE,
        user: MYSQL_USER,
        password: MYSQL_PASSWORD,
        waitForConnections: true,
        connectionLimit: 10
    }
}