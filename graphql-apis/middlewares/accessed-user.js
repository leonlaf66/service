import knex from 'knex'
import config from 'config'

export default async (ctx, next) => {
  const knexDb = knex(config.db)
  
  const accessToken = ctx.headers['access-token']
  if (accessToken) {
    let user = await knexDb('member')
      .select('id')
      .where('access_token', accessToken)
      .first()

    if (user) {
      ctx.headers.user = {
        id: user.id,
        access_token: accessToken
      }
    } else {
      ctx.body = {
        'error': 'The user has authentication failed!'
      }
      return
    }
  }
  
  return next()
}
