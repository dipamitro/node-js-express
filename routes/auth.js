'use strict';

const express = require('express');
const { login, register, forgotPassword, resetPassword } = require('../app/Http/Controllers/Auth/AuthController');
const auth = require('../app/Http/Middleware/Authenticate');

const Route = express.Router();

Route.post('/login', login);
Route.post('/register', register);
Route.post('/forgot-password', auth, forgotPassword);
Route.post('/reset-password', resetPassword);

module.exports = {
    routes: Route
}
