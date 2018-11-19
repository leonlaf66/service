export default (query, first, skip) => {
  query.limit(first).offset(skip)

  return query.clone()
    .clearSelect()
    .clearOrder()
    .limit(1)
    .offset(0)
    .count()
    .first()
    .then(row => row.count)
    .then(total => ({total, items: query}))
}
