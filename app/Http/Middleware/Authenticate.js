'use strict';

const jwt = require("jsonwebtoken");
const db = require('../../../config/mysql');

const auth = (req, res, next) => {
    // try {
    //     if (!req.headers.authorization || !req.headers.authorization.startsWith('Bearer') || !req.headers.authorization.split(' ')[1]) {
    //     return res.status(422).json({
    //         message: "please provide the token",
    //     });
    // }

    // const theToken = req.headers.authorization.split(' ')[1];
    // const decoded = jwt.verify(theToken, 'the-super-strong-secrect');
    // db.query('SELECT * FROM users where id=?', decoded.id, function (error, results, fields) {
    //     if (error) throw error;

    //     return res.send({
    //         error: false,
    //         data: results[0],
    //         message: 'Fetch Successfully.'
    //     });
    // });

    // next();
    // } catch (error) {
    //     next(error.message);
    // }
    
    const { authorization } = req.headers;

    try {
        const token = authorization.split(' ')[1];
        const decoded = jwt.verify(token, 'the-super-strong-secrect')
        const { username, id } = decoded;
        req.username = username;
        req.id = id;
        next();
    } catch (error) {
        next(error.message);
    }
}

module.exports = auth;