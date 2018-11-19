import { select as xpath } from 'xpath'

function getSchoolNames(d, typeName) {
  return xpath('/Listing/Location/Community/Schools/School', d).filter(school => {
    return xpath('SchoolCategory/text()', school).toString() === typeName
      && xpath('Name', school).length > 0
  }).map(school => {
    return xpath('Name/text()', school).toString()
  }).join(',')
}

export default {
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
    flat: true,
    value (d, ctx) {
      return xpath('/Listing/Expenses/Expense', d).map(expense => {
        let name = xpath('ExpenseCategory/text()', expense).toString()
        const value = xpath('ExpenseValue/text()', expense).toString()

        if (ctx.lang === 'zh-CN') {
          const dicts = ctx.staticData('listhub/langs/enums/ExpenseType')
          if (dicts[name]) name = dicts[name]
        }

        return {
          title: name,
          value: value,
          prefix: '$',
          'zh-CN': {
            prefix: null,
            suffix: '美元'
          }
        }
      })
    }
  }
}