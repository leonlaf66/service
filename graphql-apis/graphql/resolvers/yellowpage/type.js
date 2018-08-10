import knex from 'local/knex'
import config from 'config'

export function all(root, args, ctx) {
  return knex('taxonomy_term')
    .select('id', 'name', 'name_zh')
    .where('taxonomy_id', 2)
    .where('status', 0)
    .orderBy('sort_order', 'ASC')
}

export function getName(data, args, ctx) {
  if (ctx.lang === 'zh-CN') {
    return data.name_zh
  }
  return data.name.split('[')[0]
}

export function getPhoto({ name }) {
  const photoFile = name.match(/\[(.*)\]/)[1]
  return `${config.media.baseUrl}/yellowpage/types/${photoFile}`
}