const vGet = require('local/object-get')

module.exports = {
  elementary_school_names: {
    value (d) {
      return getSchoolNames(d, 'Elementary')
    }
  },
  middle_school_names: {
    value (d) {
      return getSchoolNames(d, 'Middle')
    }
  },
  high_school_names: {
    value (d) {
      return getSchoolNames(d, 'High')
    }
  },
  expenses: {
    value (d) {
      let expenses = vGet(d, 'Expenses[0].Expense').val([]).map(expense => { //测试 ga:5888210
        const name = vGet(expense, 'ExpenseCategory[0]').val()
        const value = vGet(expense, 'ExpenseValue[0]').val()
        return {
          name,
          value
        }
      })
      return expenses.length > 0 ? expenses : null
    }
  }
}

function getSchoolNames(d, typeName) { // 测试  ca:OC17214717
  let scrhooNames = vGet(d, 'Location[0].Community[0].Schools[0].School').val([]).filter(school => {
    return vGet(school, 'SchoolCategory[0]').val() === typeName
      && vGet(school, 'Name[0]').val('').length > 0
  }).map(school => {
    return vGet(school, 'Name[0]').val('')
  }).join(',')
  return scrhooNames !== '' ? scrhooNames : null
}
