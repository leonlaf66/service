import baseRules from './fields.base.json'
import mlsBaseRules from './mls/detail.fields.base.json'
import listhubRules from './fields.listhub.js'
import { merge } from 'lodash'


export const mls = merge(baseRules, mlsBaseRules)
export const listhub = merge(baseRules, listhubRules)