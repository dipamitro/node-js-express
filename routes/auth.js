'use strict';

const express = require('express');

const Route = express.Router();

Route.get('/auth', (req, res, next) => {
    res.status(200).send(`Hello World !!!`);
})

module.exports = {
    routes: Route
}
