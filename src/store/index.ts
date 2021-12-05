import { createPinia, defineStore } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { ApiServer, apiServer, Qnote, QnotePartial, Stats, Tags } from '@/api/server.api'

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

// Auth
export const useAuth = defineStore('auth', {
  persist: true,

  state: () => ({ ...{ apiKey: null as null | string } }),

  actions: {},
})

// Stats
export const useStats = defineStore('stats', {
  state: () => ({ ...{ stats: null as null | Stats, needReload: false } }),

  actions: {
    async fetchStats(force = false) {
      if (!force && !this.needReload && this.stats) return
      const res = await apiServer.fetchStats()
      const { stats } = res.data
      if (stats?.last_qnote) ApiServer.populateQnote(stats.last_qnote)
      if (stats?.older_qnote) ApiServer.populateQnote(stats.older_qnote)
      if (stats) this.$patch({ stats, needReload: false })
    },
  },
})

// Tags
export const useTags = defineStore('tags', {
  state: () => ({ ...{ tags: null as null | Tags, needReload: false } }),

  actions: {
    async fetchStats(force = false) {
      if (!force && !this.needReload && this.tags) return
      const res = await apiServer.fetchTags()
      const { tags } = res.data
      if (tags) this.$patch({ tags, needReload: false })
    },
  },
})

// Qnotes
export const useQnotes = defineStore('qnotes', {
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
        ApiServer.populateQnote(...qnotes)
        this.$patch({ qnotes, needReload: false })
      }
    },

    async fetchQnote(qnoteID: number) {
      const {
        data: { qnote },
      } = await apiServer.fetchQnote(qnoteID)
      if (qnote) ApiServer.populateQnote(qnote)
      return qnote as Qnote | null
    },

    async searchQnotes(q?: string, tags?: string) {
      const {
        data: { qnotes },
      } = await apiServer.searchQnotes(q, tags)
      if (qnotes) {
        ApiServer.populateQnote(...qnotes)
        this.$patch({ searchedQnotes: qnotes })
      }
    },

    async createQnote(qnote: QnotePartial) {
      const { data } = await apiServer.createQnote(qnote)
      if (data && data.message) {
        this.onQnotesChange()
      }
    },

    async updateQnote(qnoteID: number, qnote: QnotePartial) {
      const { data } = await apiServer.updateQnote(qnoteID, qnote)
      if (data && data.message) {
        this.onQnotesChange()
      }
    },

    async deleteQnote(qnoteID: number) {
      const { data } = await apiServer.deleteQnote(qnoteID)
      if (data && data.message) {
        this.onQnotesChange()
      }
    },

    onQnotesChange() {
      const statsStore = useStats()
      const tagsStore = useTags()
      this.$patch({ needReload: true })
      statsStore.$patch({ needReload: true })
      tagsStore.$patch({ needReload: true })
    },
  },
})

// Popup
export const usePopup = defineStore('popup', {
  state: () => ({ ...{ is401: false, hasNeedCredential: false } }),

  actions: {},
})

export { mapActions, mapState, mapStores, mapWritableState } from 'pinia'

export default pinia
