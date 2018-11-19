import { GraphQLScalarType } from 'graphql'
import moment from 'moment'

export default new GraphQLScalarType({
  name: 'Date',
  description: 'Date custom scalar type',
  parseValue(value) {
    return new Date(value) // value from the client
  },
  serialize(value) {
    return moment(value).format("YYYY-MM-DD hh:mm:ss")
  },
  parseLiteral(ast) {
    return act.value // ast value is always in string format
  },
})