import { makeExecutableSchema } from 'graphql-tools'
import { graphqls2s } from 'graphql-s2s'
import GraphQLJSON from 'graphql-type-json'
import { fileLoader, mergeTypes } from 'merge-graphql-schemas'
import fs from 'fs'
import { merge } from 'lodash'

import DateScalarType from './scalars/date'

const syncTypeDefs = mergeTypes(fileLoader(__dirname.concat('/schemas')), { all: true })

const syncResolvers = fs.readdirSync(__dirname.concat('/resolvers')).filter(file => {
  return file.endsWith('.js')
}).map(file => {
  return require(`./resolvers/${file}`)
})

const resolvers = merge({}, {
    JSON: GraphQLJSON,
    Date: DateScalarType
  },
  ...syncResolvers
)

module.exports = makeExecutableSchema({
  typeDefs: [graphqls2s.transpileSchema(syncTypeDefs)],
  resolvers: resolvers,
  resolverValidationOptions: {
    requireResolversForResolveType: false
  }
})

