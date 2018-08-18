import knex from 'local/knex'
import * as loaders from './area/loaders'
import staticData from 'local/static-data'
import config from 'config'

const totalsFor = async (areaId, ...propArgs) => {
  return await knex('house_index_v2')
    .where('area_id', areaId)
    .where(...propArgs)
    .where('is_online_abled', true)
    .count('list_no')
    .first()
    .then(r => r.count)
}

module.exports = {
  Area: {
    name: (d, _, { tt }) => tt(d.name),
    desc: (d, _, { tt }) => d.desc ? tt(d.desc) : null,
    img_src: ({ id }) => `${config.media.baseUrl}/area/${id}.jpg`,
    summaries: d => d,
    settings: d => d.__is_detail ? loaders.settings(d.id) : null,
    top_houses: d => loaders.topHouse.load(d.id),
    lastest_houses: (d, { first }) => loaders.lastestHouse(first).load(d.id),
    lastest_news: (d, { first }) => loaders.lastestNews(first).load(d.id)
  },
  AreaSummary: {
    async house_sells (area) {
      if (area.__is_detail) {
        return await totalsFor(area.id, 'prop_type', '<>', 'RN')
      }
    },
    async house_rents (area) {
      if (area.__is_detail) {
        return await totalsFor(area.id, 'prop_type', 'RN')
      }
    },
    async house_markets (area) {
      if (area.__is_detail) {
        return loaders.summaries(area.id)
      }
    }
  },
  Query: {
    areas,
    area
  }
}

/**
 * 列表
 */
async function areas () {
  return staticData('areas')
}

/**
 * 单个
 */
function area (_, { id }) {
  const data = staticData('areas').find(r => r.id === id)
  data.__is_detail = true
  return data
}