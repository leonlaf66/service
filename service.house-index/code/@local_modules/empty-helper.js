const empty = val => {
  if (typeof val === 'undefined') return true
  if (!val) return true
  if (val instanceof String && val.replace(/\s+/g, '') === '') return true
  return false 
}

const notEmpty = val => {
  return !empty(val)
}

const emptyIf = (val, tval = undefined, fval = null) => {
  if (tval === undefined) tval = val

  return notEmpty(val)
    ? tval
    : fval
}

module.exports = {
  empty,
  notEmpty,
  emptyIf
}
