export default {
  img_src (d) {
    const reg = /<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/
    let res = d.content.match(reg)
    if (res) {
      return res[1]
    }
  },
  intro (d, { length }) {
    const content = d.content.replace(/<[^>]*>/g, '')
    return content.substring(0, length) + '...'
  }
}