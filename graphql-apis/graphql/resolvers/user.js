import knex from 'local/knex'
import md5 from 'md5'
import _ from 'lodash'

module.exports = {
  Mutation: {
    login,
    register
  }
}

function login (root, { username, password }, ctx) {
  return knex('member')
    .where('email', username)
    .where('password', md5(password))
    .first()
}

function register (root, { form }) {
  return false
}