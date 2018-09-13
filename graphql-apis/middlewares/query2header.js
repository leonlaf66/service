export default async (ctx, next) => {
  ['appid', 'ip-address', 'app-token', 'area-id', 'language', 'access-token'].forEach(field => {
    if (Object.keys(ctx.query).includes(field)) {
      ctx.headers[field] = ctx.query[field]
    }
  })
  
  return next()
}