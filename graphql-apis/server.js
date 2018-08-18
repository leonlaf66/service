import cpusReady from './cluster'
import Koa from 'koa'
import { ApolloServer } from 'apollo-server-koa'
import knex from 'knex'
import cors from 'koa2-cors'
import schema from './graphql'
import staticData from './data'
import config from 'config'

const knexDb = knex(config.db)

cpusReady ( () => {
  const server = new ApolloServer({
    schema,
    context: ({ ctx }) => ({
      headers: ctx.req.headers,
      area_id: ctx.req.headers['area-id'],
      app_token: ctx.req.headers['app-token'],
      lang: ctx.req.headers.language,
      access_token: ctx.req.headers['access-token'],
      user: ctx.headers.user,
      staticData,
      tt: (en, zh) => {
        if (en instanceof Array) { zh = en[1]; en = en[0] }
        if (!en) { en = zh }
        if (!zh) { zh = en }
        return ctx.req.headers.language === 'en-US' ? en : zh
      }
    }),
    playground: {
      settings: {
        'editor.cursorShape': 'line'
      }
    },
    introspection: true
  })

  const app = new Koa()

  // 具体参数我们在后面进行解释
  app.use(cors({
      origin: function (ctx) {
          return "*"; // 允许来自所有域名请求
      },
      exposeHeaders: ['WWW-Authenticate', 'Server-Authorization'],
      maxAge: 5,
      credentials: true,
      allowMethods: ['GET', 'POST', 'OPTIONS'],
      allowHeaders: ['Content-Type', 'Authorization', 'Accept'],
  }))

  app.use(async (ctx, next) => {
    if (ctx.headers['app-token'] !== config.appToken) {
      ctx.body = {
        'error': 'Authentication failed!'
      }
      return
    }

    if (!ctx.headers.language) {
      ctx.headers.language = 'zh-CN'
    }

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

    await next()
  })

  server.applyMiddleware({ app })

  const port = config.server.port
  app.listen({ port }, () =>
    console.log(`🚀 Server ready at http://localhost:${port}${server.graphqlPath}`),
  )
})