module.exports = (message, code = 500) => {
  return {
    error: message,
    code: code
  }
}