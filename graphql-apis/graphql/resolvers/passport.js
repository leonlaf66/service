import { AuthenticationError } from 'apollo-server'
import knex from 'local/knex'
import md5 from 'md5'
import moment from 'moment'
import validate from 'local/validator'
import isEmail from 'validator/lib/isEmail'
import _ from 'lodash'

module.exports = {
  Mutation: {
    login,
    openid_login,
    register,
    reset_password
  }
}

/**
 * 普通登陆
 */
async function login (_, data, ctx) {
  const member = await knex('member')
    .where('email', data.username)
    .first()

  const validateResult = validate({ username: 'checkLogin' }, {
    checkLogin () {
      if (! member || md5(data.password) !== member.password) {
        return ['incorrect username or password.', '不存在的用户或错误的密码!']
      }
    }
  })(data, ctx)

  return {
    success: validateResult === true,
    errors: validateResult === true ? false : validateResult,
    member: validateResult === true ? member : null
  }
}

/**
 * Open id登陆、注册
 */
async function openid_login (root, { open_id }, { tt }) {
  if (open_id.length < 20) {
    throw new AuthenticationError(tt('Incorrect open id.', '错误的open id!'))
  }

  let member = await knex('member')
    .where('open_id', open_id)
    .first()

  if (member) {
    return member
  }

  const now = moment().format('YYYY-MM-DD hh:mm:ss')
  const resp = await knex('member')
    .returning('*')
    .insert({
      auth_key: md5(now),
      access_token: md5(now),
      created_at: now,
      updated_at: now,
      registration_ip: '',
      open_id: open_id,
      confirmed_at: now
    })

  return resp[0]
}

/**
 * 普通注册
 */
function register (root, { form }, ctx) {
  const rules = {
    email: 'required|email',
    password: 'required|length:6,16',
    confirm_password: 'confirm_password'
  }

  const validates = {
    confirm_password (val) {
      if (form.password !== val) {
        return ['two password inconsistency.', '两次密码输入不相符.']
      }
    }
  }

  const validateResult = validate(rules, validates)(form, ctx)

  const now = moment().format('YYYY-MM-DD hh:mm:ss')
  let resp = knex('member')
    .returning('*')
    .insert({
      email,
      password: md5(form.password),
      auth_key: md5(now),
      access_token: md5(now),
      created_at: now,
      updated_at: now,
      registration_ip: '',
      confirmed_at: now
    })

  const member = resp[0]
  return {
    success: validateResult === true,
    errors: validateResult !== true ? validateResult : false,
    member
  }
}

/**
 * 重置密码 (根据email生成重置密码链接，打开链接在网页中输入新密码进行重置)
 */
function reset_password (root, { email }, ctx) {
  return false;
}
