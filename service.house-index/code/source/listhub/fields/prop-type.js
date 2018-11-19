const rules = {
  RN: name => { //RN
    return name === 'Rental'
  },
  MF: name => { //MF
    return name === 'MultiFamily'
  },
  SF: name => { //SF
    return ['Single Family Attached', 'Single Family Detached'].includes(name)
  },
  CC: name => { //CC
    return ['Condominium', 'Apartment'].includes(name)
  },
  CI: name => { //CI
    return name === 'Commercial'
  },
  BU: name => { //BU
    return name === '...' // 没有这种类型，占位
  },
  LD: name => { //LD
    return ['Lots And Land', 'Lots And Land Other'].includes(name) 
  }
};

module.exports = (name, subName) => {
  for(let propIdx in rules) {
    if (rules[propIdx](name)) {
      return propIdx
    }
  }

  for(let propIdx in rules) {
    if (rules[propIdx](subName)) {
      return propIdx
    }
  }

  throw new Error(`非法的房源类型(${name} / ${subName})`);

  return null
};