const knex = require('local/knex').db_source;
const xml2js = require('node-xml2js-promise');
const rules = require('./rules');
const replaceString = require('replace-string')

async function mapTo(sourceRows) {
  let resultItems = [];

  for (let i in sourceRows) {
    let sourceRow = sourceRows[i];
    let targetRow = {};

    await load_xml2js(sourceRow, 'json_data');

    for (let field in rules) {
      let result = (rules[field])(sourceRow, targetRow);
      if (result && typeof result === 'object' && result.constructor.name === 'Promise') {
        result = await result;
      }
      targetRow[field] = result;
    }

    resultItems.push(targetRow);
  }
  return resultItems;
}

async function load_xml2js(data, josn_field) {
  [' xmlns="http://rets.org/xsd/Syndication/2012-03" xmlns:commons="http://rets.org/xsd/RETSCommons"', 'commons:'].forEach(clearTag => {
    data.xml = replaceString(data.xml, clearTag, '')
  })
  data[josn_field] = (await xml2js(`<?xml version="1.0" encoding="UTF-8"?>${data.xml}</xml>`, {
    explicitRoot: false,
    ignoreAttrs: true
  }))
  delete data.xml;
}

module.exports = (lastAt, size) => {
  return knex('mls_rets_listhub')
    .limit(size)
    .then(mapTo);
};