import config from 'config'
import knex from 'local/knex'
import pagination from 'local/pagination'
import staticData from 'local/static-data'
import * as type from './yellowpage/type'
import { yellowPage as yPLoader } from './yellowpage/loaders'

module.exports = {
  YellowPage: {
    photo: getPhoto
  },
  GroupYellowPage: {
    items: groupItems
  },
  YellowPageType: {
    name: type.getName,
    photo: type.getPhoto
  },
  Query: {
    yellow_page_types: type.all,
    grouped_yellow_page: groupedList,
    top_yellow_pages: top,
    search_yellow_page: search
  },
  Mutation: {
    yellow_page_hit: hit
  }
}

function search (_, { type_id, first, skip }, ctx) {
  let query = knex('yellow_page')
    .where('area_id', ctx.area_id)
    .orderBy('weight', 'desc')
    .orderBy('rating', 'desc')
    .orderBy('id', 'desc')

  return pagination(query, first, skip)
}

function groupedList(_, args, { area_id }) {
  return staticData(`home.yellowpage.top.${area_id}`)
}

function groupItems({ ids }) {
  ids = ids.map(id => Number.parseInt(id))
  return yPLoader.loadMany(ids)
}

function top(_, args, ctx) {
  return knex('yellow_page')
    .where('area_id', ctx.area_id)
    .where('is_top', 1)
}

function hit(_, { data }, ctx) {
  // 
}

function getPhoto() {
  return `${config.media.baseUrl}/yellowpage/placeholder.jpg`
}