import { createStore } from 'vuex'
import axios from 'axios'

const serverURL = 'http://localhost:8081/api/'
const server = axios.create({
  baseURL: serverURL,
  headers: { 'API-Key': 'foobar' },
  // withCredentials: true,
})

export default createStore({
  state: {
    stats: null,
    qnotes: null,
    needReload: { stats: false, qnotes: false },
  },
  mutations: {
    setStats(state, stats) {
      state.stats = stats || null
    },

    setQnotes(state, qnotes) {
      if (qnotes) {
        for (const qnote of qnotes) {
          if (qnote.date) qnote.date = new Date(qnote.date)
        }
      }
      state.qnotes = qnotes || null
    },

    setNeedReload(state, reload) {
      state.needReload = { ...state.needReload, ...reload }
    },
  },
  actions: {
    async loadStats({ commit, state }, force = false) {
      if (!force && !state.needReload.stats && state.stats) return
      const { data } = await server.get('stats')
      if (data && data.stats) {
        commit('setStats', data.stats)
        commit('setNeedReload', { stats: false })
      }
    },

    async loadQnotes({ commit, state }, force = false) {
      if (!force && !state.needReload.qnotes && state.qnotes) return
      const { data } = await server.get('qnotes')
      if (data && data.qnotes) {
        commit('setQnotes', data.qnotes)
        commit('setNeedReload', { qnotes: false })
      }
    },

    async createQnote({ commit }, qnote) {
      const form = new FormData()
      Object.keys(qnote).forEach((key) => {
        form.append(key, qnote[key])
      })
      const { data } = await server.post('qnote', form)
      if (data && data.message) commit('setNeedReload', { stats: true, qnotes: true })
    },
  },
  modules: {},
})
