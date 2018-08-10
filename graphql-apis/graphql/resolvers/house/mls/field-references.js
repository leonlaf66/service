import knex from 'local/knex'
import Cache from 'cache'
import cache from 'local/ttl-cache'

const rowBuild = (items) => {
  const map = {}

  items.forEach(item => {
    const field = item.field.toLowerCase()

    if(!map[field]) map[field] = {}

    let value = [item.long, item.long_cn]

    if (item.short) {
      map[field][item.short] = value
    }
    if (item.medium) {
      map[field][item.medium] = value
    }
  })

  return map
}
const getFieldsForType = (prop) => {
  prop = prop.toLowerCase()
  const cacheKey = `mls.field.reference-${prop}`

  let result = cache.get(cacheKey)
  if (!result) {
    result = knex('house_field_reference')
      .select('field', 'short', 'medium', 'long', 'long_cn')
      .where(prop, '1')
      .then(rowBuild)

    cache.set(cacheKey, result)
  }

  return result
}

export default getFieldsForType