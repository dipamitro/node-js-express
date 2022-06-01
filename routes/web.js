'use strict';

const express = require('express');
const { users } = require('../app/Http/Controllers/UserController');

const Route = express.Router();

Route.get('/q', (req, res, next) => {
    res.status(200).send(`Hello World !!!`);
})

Route.get('/users', users);

module.exports = {
    routes: Route
}
