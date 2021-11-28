import { createPinia, defineStore } from 'pinia'
// import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { apiServer, Qnote, QnotePartial, Stats } from '@/api/server.api'

const pinia = createPinia()

// pinia.use(piniaPluginPersistedstate)

// Stats
export const useStats = defineStore('stats', {
  // persist: true,

  state: () => ({ ...{ stats: null as null | Stats, needReload: false } }),

  actions: {
    async fetchStats(force = false) {
      if (!force && !this.needReload && this.stats) return
      const res = await apiServer.fetchStats()
      const { stats } = res.data
      if (stats) this.$patch({ stats, needReload: false })
    },
  },
})

// Qnotes
export const useQnotes = defineStore('qnotes', {
  // persist: true,

  state: () => ({
    ...{
      qnotes: null as null | Qnote[],
      searchedQnotes: null as null | Qnote[],
      needReload: false,
    },
  }),

  actions: {
    async fetchQnotes(force = false) {
      if (!force && !this.needReload && this.qnotes) return
      const {
        data: { qnotes },
      } = await apiServer.fetchQnotes()
      if (qnotes) {
        for (const qnote of qnotes) {
          if (qnote.date) qnote.parseDate = new Date(qnote.date)
        }
        this.$patch({ qnotes, needReload: false })
      }
    },

    async fetchQnote(qnoteID: number) {
      const {
        data: { qnote },
      } = await apiServer.fetchQnote(qnoteID)
      if (qnote.date) qnote.parseDate = new Date(qnote.date)
      return qnote
    },

    async searchQnotes(q?: string, tags?: string) {
      const {
        data: { qnotes },
      } = await apiServer.searchQnotes(q, tags)
      if (qnotes) {
        for (const qnote of qnotes) {
          if (qnote.date) qnote.parseDate = new Date(qnote.date)
        }
        this.$patch({ searchedQnotes: qnotes })
      }
    },

    async createQnote(qnote: QnotePartial) {
      const { data } = await apiServer.createQnote(qnote)
      if (data && data.message) {
        this.$patch({ needReload: true })
        const statsStore = useStats()
        statsStore.$patch({ needReload: true })
      }
    },
  },
})

// Popup
export const usePopup = defineStore('popup', {
  // persist: false,

  state: () => ({ ...{ is401: false } }),

  actions: {},
})

export { mapActions, mapState, mapStores, mapWritableState } from 'pinia'

export default pinia
