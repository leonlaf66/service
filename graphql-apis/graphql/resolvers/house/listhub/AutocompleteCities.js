import knex from 'local/knex'

export default async (areaId) => {
  const stateId = areaId.toUpperCase()

  const rows = await knex('city')
    .where('state', stateId)
    .orderBy('type_rule', 'ASC')
    .orderBy('id', 'ASC')

  let zipCodeItems = {}
  let items = {}

  for (let k in rows) {
    let row = rows[k]
    let name = row.name

    items[name] = {
      'title': name,
      'desc': (row.name_cn ? row.name_cn : '暂无中文名') + ',' + stateId
    }

    let nameCn = row.name_cn
    if (nameCn) {
      items[nameCn] = {
          'title': nameCn,
          'desc': row.name  + ',' + stateId
      }
    }

    // 建立城市/zipcode映射
    const zipCodes = (row.zip_codes + '').replace(/[\{\}]/, '').split(',')
    for (let v in zipCodes) {
      let zipCode = zipCodes[v]
      if (!zipCodeItems[zipCode]) { // 只取第一个
          zipCodeItems[zipCode] = row
      }
    }
  }

  for (let zipCode in zipCodeItems) {
    let row = zipCodeItems[zipCode]
    zipCode = ('00000' + zipCode).slice(-5)

    items[zipCode] = {
      'title': zipCode,
      'desc': row.name + ',' + (row.name_cn ? row.name_cn : '暂无中文名') + ',' + stateId
    }
  }

  return Object.values(items)
}