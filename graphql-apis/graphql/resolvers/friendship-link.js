import knex from 'local/knex'

const buildLink = content => {
  let [_, title, url] = /\[(.*)\](.*)/.exec(content)
  return { title, url }
}

const buildLinks = content => {
  return content.split("\r\n")
    .map(d => buildLink(d))
    .filter(d => d)
}

module.exports = {
  Query: {
    friendship_links () {
      return knex('site_setting')
        .where('path', 'friended.links')
        .first()
        .get('value')
        .then(data => JSON.parse(data))
        .then(data => buildLinks(data))
    }
  }
}
