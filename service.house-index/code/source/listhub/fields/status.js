const rules = {
    'Active': 'ACT',
    'Cancelled': 'CAN',
    'Closed': 'CLO',
    'Expired': 'EXP',
    'Pending': 'PEN',
    'Withdrawn': 'WDN',
    'Sold': 'SLD'
};

module.exports = name => {
  for (let name in rules) {
    if (rules.hasOwnProperty(name)) {
      return rules[name]
    }
  }
  return null;
};