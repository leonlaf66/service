import knex from 'local/knex'
import pagination from 'local/pagination'
import { memberLoader } from './member/loaders'

module.exports = {
  Comment: {
    content: d => d.comments,
    user: d => memberLoader.load(d.user_id)
  },
  Query: {
    find_comments
  },
  Mutation: {
    created_comment
  }
}

async function find_comments (_, { page_type, type_id }) {
  // 获取pageId
  const page_url = `${page_type}/${type_id}`
  let typeRow = await knex('comment_page').where('url', page_url).first()
  if (!typeRow) {
    return {
      id: null,
      rating: null,
      results: {
        total: 0,
        page: 1,
        page_size: 15,
        page_count: 0,
        results: []
      }
    }
  }
  let pageId = typeRow.id

  // 获取平均分
  const avgRating = await knex('comment')
    .avg('rating')
    .where('page_id', pageId)
    .first()
    .get('avg')

  // 获取comments
  let resQuery = knex('comment')
    .where('page_id', pageId)
    .orderBy('created_at', 'desc')

  return {
    id: pageId,
    rating: avgRating,
    results: pagination(resQuery)
  }
}

async function created_comment (_, { page_type, type_id, content, rating }, { user }) {
  // 获取或创建page
  const page_url = `${page_type}/${type_id}`
  let typeRow = await knex('comment_page')
    .where('url', page_url)
    .first()
  let pageId = null 
  if (! typeRow) {
    pageId = await knex('comment_page')
      .returning('id')
      .insert({
        hash: '...',
        url: page_url
      })
      .then(ids => ids[0])
  } else {
    pageId = typeRow.id
  }

  // 创建comment
  let commentId = await knex('comment')
    .returning('id')
    .insert({
      'page_id': pageId,
      'rating': rating,
      'user_id': user.id,
      'comments': content,
      'created_at': knex.raw('now()')
    }).then(ids => ids[0])

  return knex('comment')
    .where('id', commentId)
    .first()
}