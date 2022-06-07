'use strict';

const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');
const db = require('../../../../config/mysql');

const login = async (req, res, next) => {
    try {
        db.query(`SELECT * FROM users WHERE username = ${db.escape(req.body.username)};`, (err, result) => {
            if (err) {
                throw err;
                return res.status(400).send({
                    msg: err
                });
            }

            if (!result.length) {
                return res.status(401).send({
                    msg: 'username or password is incorrect!'
                });
            }

            bcrypt.compare(req.body.password, result[0]['password'], (bcryptErr, bcryptResult) => {
                if (bcryptErr) {
                    throw bcryptErr;
                    return res.status(401).send({
                        msg: 'username or password is incorrect!'
                    });
                }

                if (bcryptResult) {
                    const token = jwt.sign({id:result[0].id},'the-super-strong-secrect',{ expiresIn: '1h' });
                    db.query(`UPDATE users SET last_login = now() WHERE id = '${result[0].id}'`);

                    return res.status(200).send({
                        msg: 'logged in!',
                        token,
                        user: result[0]
                    });
                }

                return res.status(401).send({
                    msg: 'username or password is incorrect!'
                });
            });
        });
    } catch (error) {
        res.status(400).send(error.message);
    }
}

const register = async (req, res, next) => {
    try {
        db.query(`SELECT * FROM users WHERE LOWER(username) = LOWER(${db.escape(req.body.username)});`, (err, result) => {
            if (result.length) {
                return res.status(409).send({
                    msg: 'this username is already in use!'
                });
            }
            else {
                bcrypt.hash(req.body.password, 10, (err, hash) => {
                    if (err) {
                        return res.status(500).send({
                            msg: err
                        });
                    }
                    else {
                        db.query(`INSERT INTO users (name, username, email, password, phone) 
                                VALUES ('${req.body.name}', '${req.body.username}', '${req.body.email}', ${db.escape(hash)}, '${req.body.phone}')`,
                            (err, result) => {
                                if (err) {
                                    throw err;
                                    return res.status(400).send({
                                        msg: err
                                    });
                                }
                                return res.status(201).send({
                                    msg: 'registerd !!!'
                                });
                            }
                        );
                    }
                });
            }
        });
    } catch (error) {
        res.status(400).send(error.message);
    }
}

const forgotPassword = async (req, res, next) => {
    try {
        
    } catch (error) {
        res.status(400).send(error.message);
    }
}

const resetPassword = async (req, res, next) => {
    try {
        res.send('ok');
    } catch (error) {
        res.status(400).send(error.message);
    }
}

module.exports = {
    login,
    register,
    forgotPassword,
    resetPassword
}