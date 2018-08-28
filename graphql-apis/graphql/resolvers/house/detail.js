import knex from 'local/knex'
import { xmlRender, xpath} from 'local/xml'
import xmljs from 'xml-js'
import mlsDetail from './mls/house-detail'
import mlsFieldReferences from './mls/field-references'
import listhubDetail from './listhub/house-detail'

export default async (d, args, ctx) => {
  if (!d.__is_detail) return []

  const house = await getHouseData(d.area_id, d.list_no)
  const rules = await getRenderRules(d.area_id, d.prop_type)

  if (d.area_id === 'ma') {
    const referenceFields = await mlsFieldReferences(d.prop_type)
    return await mlsDetail(house, rules, referenceFields, ctx)
  }
  
  return await listhubDetail(house, rules, ctx)
}

async function getHouseData(areaId, listNo) {
  return await knex('house_data')
    .where('list_no', listNo)
    .first()
    .then(async r => {
      if (areaId === 'ma') {
        return JSON.parse(r.orgi_data)
      }
      return xmlRender(`<?xml version="1.0" encoding="UTF-8"?>${r.orgi_data}</xml>`)
    })
}

async function getRenderRules(areaId, propType) {
  const table = areaId === 'ma'
    ? 'house_field_prop_rule'
    : 'listhub_house_field_prop_rule'

  const row = await knex(table)
    .where('type_id', propType.toLowerCase())
    .select('xml_rules')
    .first()

  return xmljs.xml2js(`<groups>${row.xml_rules}</groups>`, {
    compact: true
  })
}