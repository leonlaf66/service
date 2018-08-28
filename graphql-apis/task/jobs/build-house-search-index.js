const knex = require('./@knex')

const rules = {
  propTypes: {
    'RN': 0,
    'SF': 1,
    "MF": 2,
    "CC": 3,
    "CI": 4,
    "BU": 5,
    "LD": 6
  },
  price: {
    1: [0, 500000],
    2: [500000, 1000000],
    3: [1000000, 1500000],
    4: [1500000, 2000000],
    5: [2000000, 99999999999999]
  },
}

const filters = {
  list_no: (d) => d.list_no,
  prop_type_idx: (d) => d.prop_type ? rules.propTypes[d.prop_type] : null,
  price_sec_idx: (d) => {
    
  },
  price_sec_cn_idx: (d) => {

  }
}

function rowProcess (row) {
  let data = {}

  for (let field in filters) {
    data[field] = (filters[field])(row)
  }

  console.log(data)
  process.exit(0)
}

const main = async () => {
  const baseQuery = knex('house_index_v2')
    .where('is_online_abled', true)
    .orderBy('list_date', 'DESC')
    .orderBy('list_no', 'ASC')

  const total = await baseQuery.clone()
    .clearSelect()
    .clearOrder()
    .count()
    .first()
    .then(r => r.count)

  const pageSize = 1000
  const pageCount = Math.ceil(total * 1.0 / pageSize)

  for (let page = 0; page < pageCount; page ++){
    let query = await baseQuery.clone()
      .limit(pageSize)
      .offset(page * pageCount)
      .then(rows => {
        for (let i in rows) {
          rowProcess(rows[i])
        }
      })
  }
  
  process.exit(0)
}

main()