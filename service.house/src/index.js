import { router, get, post, options } from 'microrouter';
import { ApolloServer, gql } from 'apollo-server-micro';
import { fileLoader, mergeTypes } from 'merge-graphql-schemas';
import GraphQLJSON from 'graphql-type-json';
import { merge } from 'lodash';
import DateScalarType from './scalars/date';
import accessedUser from 'local/accessed-user';
import staticData from './data';
import fs from 'fs';

let resolvers = fs.readdirSync(__dirname.concat('/resolvers')).filter(file => {
  return file.endsWith('.js');
}).map(file => {
  return require(`./resolvers/${file}`);
})

resolvers = merge({}, {
    JSON: GraphQLJSON,
    Date: DateScalarType
  },
  ...resolvers
);

const typeDefs = mergeTypes(fileLoader(__dirname.concat('/schemas')), { all: true });
const apolloServer = new ApolloServer({
  typeDefs,
  resolvers,
  context: async (ctx) => ({
    headers: ctx.req.headers,
    area_id: ctx.req.headers['area-id'],
    app_token: ctx.req.headers['app-token'],
    lang: ctx.req.headers.language,
    access_token: ctx.req.headers['access-token'],
    user: await accessedUser(ctx.req.headers['access-token']),
    staticData,
    tt: (en, zh) => {
      if (en instanceof Array) { zh = en[1]; en = en[0] }
      if (!en) { en = zh }
      if (!zh) { zh = en }
      return ctx.req.headers.language === 'en-US' ? en : zh
    }
  })
});
const graphqlPath = '/data';

export default apolloServer.createHandler({ path: graphqlPath });