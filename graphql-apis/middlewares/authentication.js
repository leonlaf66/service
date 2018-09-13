import config from 'config'

export default async (ctx, next) => {
  let appid = ctx.headers['appid']
  if (!appid) appid = 'default'
  if (config.abledApps.hasOwnProperty(appid)) {
    if (ctx.headers['app-token'] === config.abledApps[appid].appToken) {
      ctx.appid = appid
    } else {
      ctx.body = {'error': 'Authentication failed!'}
      return;
    }
  } else {
    ctx.body = {'error': 'Authentication failed!'}
    return;
  }

  return next()
}