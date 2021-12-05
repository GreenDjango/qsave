<template>
  <div class="home grid grid-cols-1 gap-6 px-8 py-5 max-w-screen-2xl mx-auto">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
      <div v-for="(stat, index) in stats" :key="index" class="card shadow-lg">
        <div class="stat">
          <div class="stat-figure text-accent-focus lg:mr-6">
            <Icon :glyph="stat.glyph" class="inline-block w-10 stroke-current" />
          </div>
          <div class="stat-title">{{ stat.title }}</div>
          <div class="stat-value">{{ stat.text.replace('%v', stat.value) }}</div>
          <div class="stat-desc">{{ stat.subText.replace('%v', stat.subValue) }}</div>
        </div>
      </div>
    </div>

    <div class="card col-span-1 row-span-3 shadow-lg bg-base-100">
      <div class="card-body px-6">
        <div class="w-full flex items-center justify-evenly flex-col md:flex-row">
          <ul v-show="Object.keys(selectTags).length" class="flex flex-wrap justify-center tags-list w-full md:w-1/4">
            <Badge
              v-for="(tag, key) in selectTags"
              :key="key"
              :toHash="tag"
              :text="tag"
              :cross="true"
              @click="onRemoveTag(key)"
              class="badge-outline cursor-pointer mb-1 mr-1"
            />
          </ul>

          <div class="relative w-full sm:w-2/3 md:w-1/3 mt-3 md:mt-0">
            <select v-model="tagPicker" class="select select-bordered w-full">
              <option disabled="" selected="" value="-1">Choose a tag</option>
              <option v-for="(tag, key) in tags" :key="key" :value="key">
                {{ tag }}
              </option>
            </select>
          </div>

          <div class="relative w-full sm:w-2/3 md:w-1/3 mt-3 md:mt-0 mb-3 md:mb-0">
            <input
              @keypress.enter="onSearch"
              ref="searchBox"
              type="text"
              placeholder="Search"
              class="w-full pr-16 text-base input input-primary input-bordered"
            />
            <button
              @click="onSearch"
              :disabled="loading"
              class="absolute right-0 rounded-l-none btn btn-primary"
              aria-label="search"
            >
              <Icon glyph="search" class="inline-block w-6 stroke-current" />
            </button>
          </div>
        </div>

        <h2 class="my-4 text-2xl font-bold card-title">Qnotes</h2>

        <Loader v-if="loading" class="w-full my-6" />

        <Table v-else-if="items && items.length" :items="items" @row-click="onRowClick" />

        <div v-else class="w-full text-center my-6">No Qnotes found</div>

        <!--
        <div class="btn-group mt-4 justify-center">
          <button class="btn btn-sm content-center">«</button>
          <button class="btn btn-sm content-center btn-active">1</button>
          <button class="btn btn-sm content-center">2</button>
          <button class="btn btn-sm content-center">3</button>
          <button class="btn btn-sm content-center btn-disabled">...</button>
          <button class="btn btn-sm content-center">100</button>
          <button class="btn btn-sm content-center">»</button>
        </div>
        -->
      </div>
    </div>
    <!-- 
    <ColorPallet />
    <img alt="Vue logo" src="@/assets/logo.svg" /> -->
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import copy from 'copy-text-to-clipboard'
import prettyBytes from 'pretty-bytes'
import { mapStores, useQnotes, useStats } from '@/store'
import { Qnote } from '@/api/server.api'
import { notify } from '@/plugin/notify'
import { stringifyError, errorTitle } from '@/utils'
import Icon from '@/components/atoms/Icon.vue'
import Badge from '@/components/atoms/Badge.vue'
import Table from '@/components/organisms/Table.vue'
import Loader from '@/components/atoms/Loader.vue'
import ColorPallet from '@/components/dedicated/ColorPallet.vue'

@Options({
  components: {
    Icon,
    Badge,
    Table,
    Loader,
    ColorPallet,
  },
  computed: {
    //...mapStores(useQnotes)
  },
})
export default class Home extends Vue {
  loading = true
  stats = {
    tq: { glyph: 'bookmark-alt', title: 'Total Qnotes', value: '0', text: '%v', subValue: 'none', subText: '↗︎ latest qnote: %v' },
    tt: { glyph: 'tag', title: 'Total tags', value: '0', text: '%v', subValue: 'none', subText: '↗︎ more use tag: %v' },
    ds: { glyph: 'database', title: 'Database size', value: '0 kB', text: '%v', subValue: 'none', subText: '↘ oldest qnote: %v' },
  }
  tags = {} as { [s: string]: string }
  tagPicker = '-1'
  selectTags = {} as { [s: string]: string }
  items: Qnote[] = []

  get qnotesStore() {
    return mapStores(useQnotes).qnotesStore()
  }

  get statsStore() {
    return mapStores(useStats).statsStore()
  }

  beforeMount() {
    this.$watch('tagPicker', this.onTagPicker)
    this.fetchStats()
    this.fetchQnotes()
  }

  onTagPicker(newVal: string) {
    if (newVal === '-1') return
    this.tagPicker = '-1'
    this.selectTags[newVal] = this.tags[newVal]
    delete this.tags[newVal]
  }

  onRemoveTag(id: string) {
    this.tags[id] = this.selectTags[id]
    delete this.selectTags[id]
  }

  resetTagPicker(newTags: string[]) {
    this.tagPicker = '-1'
    this.selectTags = {}
    this.tags = {}
    newTags.forEach((val, idx) => {
      this.tags[idx] = val
    })
  }

  async onSearch() {
    const q = (this.$refs.searchBox as HTMLInputElement).value.trim() || undefined
    const tags = Object.values(this.selectTags).sort().join(';') || undefined
    if (!q && !tags) return

    this.loading = true
    try {
      await this.qnotesStore.searchQnotes(q, tags)
      this.items = (this.qnotesStore.searchedQnotes as Qnote[]) || []
    } catch (err) {
      this.items = []
      notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 3000 })
    }
    this.loading = false
  }

  onRowClick(row: Qnote) {
    const dataToCopy = row.url || row.text || row.code || ''
    if (dataToCopy) {
      copy(String(dataToCopy))
      notify.show(`Row ${row.id} (${dataToCopy.slice(0, 20)}...) copied !`, { type: 'info', queue: false, duration: 1800 })
    }
  }

  async fetchQnotes() {
    this.loading = true
    try {
      await this.qnotesStore.fetchQnotes()
      this.items = (this.qnotesStore.qnotes as Qnote[]) || []
    } catch (err) {
      this.items = []
      notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 3000 })
    }
    this.loading = false
  }

  async fetchStats() {
    try {
      await this.statsStore.fetchStats()

      const tags = Object.keys(this.statsStore.stats?.all_tags || {})
      this.resetTagPicker(tags.sort())

      this.stats.tq.value = String(this.statsStore.stats?.total_qnotes || 0)
      this.stats.tq.subValue = this.statsStore.stats?.last_qnote?.parseDate?.toDateString()?.slice(4) || 'none'

      const bestTag = Object.entries(this.statsStore.stats?.all_tags || {}).sort(([_a, a], [_b, b]) => b - a)[0]
      this.stats.tt.value = tags.length.toString()
      this.stats.tt.subValue = bestTag?.[0] || 'none'

      this.stats.ds.value = prettyBytes(this.statsStore.stats?.db_size || 0)
      this.stats.ds.subValue = this.statsStore.stats?.older_qnote?.parseDate?.toDateString()?.slice(4) || 'none'
    } catch (err) {
      notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 3000 })
    }
  }
}
</script>

<style scoped>
.tags-list li:not(:last-child) {
  margin-right: 0.25rem;
}

.stat-title,
.stat-desc {
  opacity: 0.8;
}
</style>
