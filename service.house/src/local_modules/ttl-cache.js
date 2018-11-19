function Cache () {
  let data = {}

  this.set = function (key, value) {
    return data[key] = value
  }

  this.get = function (key) {
    return data[key]
  }
}

export default new Cache()