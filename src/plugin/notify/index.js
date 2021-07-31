import Events from './events.js'

const methods = {
  /**
   * @param  {string} message
   * @param  {{ title?: string; duration?: number; type?: 'basic' | 'info' | 'success' | 'warning' | 'error'; queue?: boolean }} options
   */
  show(message, options = {}) {
    Events.$emit('show', { text: message, ...options })
  },
  clear() {
    Events.$emit('clear')
  },
}

export { methods as notify }

const Plugin = (app) => {
  app.$notify = methods
  app.config.globalProperties.$notify = methods
}

export default { install: Plugin }
