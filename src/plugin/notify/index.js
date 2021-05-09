import Events from './events.js'

const Plugin = (app) => {
  const methods = {
    // title, text, type, duration
    show(message, options = {}) {
      Events.$emit('show', { text: message, ...options })
    },
    clear() {
      Events.$emit('clear')
    },
  }
  app.$notify = methods
  app.config.globalProperties.$notify = methods
}

export default { install: Plugin }
