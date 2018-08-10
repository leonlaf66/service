import knex from 'local/knex'
import pagination from 'local/pagination'
import renderFields from './news/@fields'
import { newsType as newsTypeLoader } from './news/loaders'
import _ from 'lodash'

module.exports = {
  News: {
    img_src: renderFields.img_src,
    intro: renderFields.intro,
    type: (news, _, { tt }) => (
      newsTypeLoader.load(news.type_id)
        .then(d => ({id: d.id, name: tt(d.name)}))
    ),
    same_type_news
  },
  Query: {
    news_cats: newsCats,
    find_news: findNews,
    news,
    nearest_news: nearestNewsList,
    hot_news: hotNewsList,
    news_banners
  }
}

function newsBaseQuery (areaId) {
  return knex('news')
    .where('area_id', '@>', `{${areaId}}`)
    .where('status', 1)
    .orderBy('created_at', 'desc')
}

function newsCats (root, args, { tt }) {
  return knex('taxonomy_term')
    .select('id', 'name', 'name_zh')
    .where('taxonomy_id', 3)
    .orderBy('sort_order', 'asc')
    .pluck('id')
    .then(ids => {
      return newsTypeLoader.loadMany(ids)
    }).then(items => {
      return _.map(items, d => {
        return {
          id: d.id,
          name: tt(d.name)
        }
      })
    })
}

/**
 * 新闻列表
 */
async function findNews (_, { type_id, page, page_size }, ctx, info) {
  // 建立查询
  let query = newsBaseQuery(ctx.area_id)
  if (type_id) {
    query.whereRaw('type_id=?', type_id)
  }

  // 建立分页
  return pagination(query, page, page_size)
}

/**
 * 获取新闻详情
 */
async function news (_, { id }, ctx, info) {
  return await newsBaseQuery(ctx.area_id)
    .where('id', id)
    .first()
    .then(d => {
      d.__is_detail = true
      return d
    })
}


/**
 * 获取最新资讯
 */
async function nearestNewsList (house, { limit }, ctx, info) {
  // 建立查询
  return await newsBaseQuery(ctx.area_id)
    .limit(limit)
}

/**
 * 获取热门新闻
 */
async function hotNewsList (_source, { limit }, ctx, info) {
  // 建立查询
  return await newsBaseQuery(ctx.area_id)
    .where('is_hot', true)
    .limit(limit)
}

/**
 * 同类型其它新闻
 */
async function same_type_news({ __is_detail, type_id, id }, { limit }) {
  if (!__is_detail) return []

  return knex('news')
    .where('type_id', type_id)
    .where('id', '<>', id)
    .orderBy('created_at', 'DESC')
    .limit(limit)
}

/**
 * 新闻banners
 */
async function news_banners (_, { app_type }, ctx) {
  let data = await knex('site_setting')
    .select('value')
    .where('site_id', ctx.area_id)
    .where('path', app_type === 'PC' ? 'news.banner.top' : 'app.news.banner.top')
    .first()
    .get('value')

  data = JSON.parse(data)

  if (!data['childrens']) data['childrens'] = []

  return data['childrens'].map(d => {
    d.news_id = Number.parseInt(d.url.replace('#', ''))
    delete d.url
    return d
  })
}