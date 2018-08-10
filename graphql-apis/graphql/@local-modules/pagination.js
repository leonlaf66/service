const create = (query, total, page, page_size) => {
  const offset = (page - 1) * page_size
  const page_count = Math.ceil(total * 1.0 / page_size)

  query.limit(page_size).offset(offset)

  return {
    total,
    page,
    page_size,
    page_count,
    items: query
  }
}

export default (query, page = 1, page_size = 15) => {
  return query.clone()
    .clearSelect()
    .clearOrder()
    .count()
    .first()
    .then(row => create(query, row.count, page, page_size))
}
