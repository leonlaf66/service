import cpusReady from './cluster'
import Koa from 'koa'
import { ApolloServer } from 'apollo-server-koa'
import koaBody from 'koa-body'
import middlewares from './middlewares'
import schema from './graphql'
import staticData from './data'
import config from 'config'

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
  playground: true,
  introspection: true
})

cpusReady ( () => {
  const app = new Koa()

  app.use(middlewares.cors)
  app.use(middlewares.query2header)
  app.use(middlewares.authentication)
  app.use(middlewares.language)
  app.use(middlewares.accessedUser)
  app.use(koaBody());
  app.use(middlewares.logger)

  app.use(async (ctx, next) => {
    server.playgroundOptions.settings['editor.cursorShape'] = 'line'
    await next()
  })
  server.applyMiddleware({ app })

  const port = config.server.port
  app.listen({ port }, () =>
    console.log(`🚀 Server ready at http://localhost:${port}${server.graphqlPath}`),
  )
})