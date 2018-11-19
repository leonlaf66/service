const crypto = require('crypto')
const error = require('local/error')

module.exports = (signs) => {
  return (ctx, next) => {
    let key = ctx.request.query.key
    if (! signs.hasOwnProperty(key)) {
      ctx.body = error('无效的认证key参数!', 401)
      return
    }

    let timeDiff = Date.now() - Number.parseInt(ctx.request.query.timestamp)
    if (timeDiff <= 0) {
      ctx.body = error('无效的timestamp参数!', 401)
      return
    }

    if (timeDiff > 15 * 60 * 1000) {
      ctx.body = error('请求已失效!', 401)
      return
    }

    let sign = crypto.createHash('md5').update(signs[key].secret + ctx.request.query.timestamp).digest('hex')
    if (sign !== ctx.request.query.sign) {
      ctx.body = error('认证失败!', 401)
      return
    }

    next()
  }
}