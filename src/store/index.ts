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
  },
  actions: {
    async loadStats({ commit, state }, force = false) {
      if (!force && state.stats) return
      const { data } = await server.get('stats')
      if (data && data.stats) commit('setStats', data.stats)
    },
    async loadQnotes({ commit, state }, force = false) {
      if (!force && state.qnotes) return
      const { data } = await server.get('qnotes')
      if (data && data.qnotes) commit('setQnotes', data.qnotes)
    },
  },
  modules: {},
})
