import knex from 'knex'
import config from 'config'
import geolite2 from 'geolite2'
import maxmind from 'maxmind'

export default async (ctx, next) => {
  await next()
  if (ctx.request.method === 'POST') {
    let ipAddress = ctx.request.headers['ip-address']
    if (!ipAddress) return

    if(ctx.request.body.operationName === 'IntrospectionQuery') return

    const geoData = getGeoData(ipAddress)

    writeLog(ctx.appid, geoData, {
      request: ctx.request.body,
      response: ctx.body
    })
  }
}

function getGeoData(ipAddress) {
  if (ipAddress === '::1') {
    return null;
  }

  const lookup = maxmind.openSync(geolite2.paths.city)
  const geo = lookup.get(ipAddress)
  if (!geo) return null

  return {
    ipAddress: ipAddress,
    city: {
      name: geo.city.names['zh-CN']
    },
    country: {
      code: geo.country.iso_code,
      name: geo.country.names['zh-CN']
    },
    location: geo.location
  }
}

function writeLog(appid, geo, data) {
  knex(config.db)('access_logs').insert({
    appid,
    geo,
    data
  }).then(() => {
    // 居然必须要这个才会自动执行
  })
}
