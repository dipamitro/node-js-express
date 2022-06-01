const mysql = require('../../../config/mysql');

const users = async (req, res, next) => {
    try {
        const users = await mysql.execute('SELECT * FROM users');
        res.status(200).send(users[0]);
    } catch (error) {
        res.status(400).send(error.message);
    }
}

module.exports = {
    users
}