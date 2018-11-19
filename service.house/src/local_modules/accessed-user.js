import knex from 'knex'
import config from 'config'

export default async (accessToken) => {
  const knexDb = knex(config.db)
  
  if (accessToken) {
    let user = await knexDb('member')
      .select('id')
      .where('access_token', accessToken)
      .first()

    if (user) {
      return {
        id: user.id,
        access_token: accessToken
      }
    } else {
      return {
        'error': 'The user has authentication failed!'
      }
    }
  }
}
