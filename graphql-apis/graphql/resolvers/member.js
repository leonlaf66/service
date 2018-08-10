import knex from 'local/knex'
import * as loaders from './member/loaders'

module.exports = {
  Member: {
    profile: getMemberProfile
  },
  Query: {
    get_member: getMemberInfo
  },
  Mutation: {
    modify_member_profile: modifyMemberProfile
  }
}

function getMemberInfo (root, args, { access_token }) {
  return knex('member')
    .where('access_token', access_token)
    .first()
}

function getMemberProfile (member) {
  return loaders.memberProfile.load(member.id)
}

function modifyMemberProfile(root, args) {
  return args.profile
}