export default async (ctx, next) => {
  if (!ctx.headers.language) {
    ctx.headers.language = 'zh-CN'
  }
  
  return next()
}