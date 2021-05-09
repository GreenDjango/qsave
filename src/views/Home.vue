<template>
  <div class="home grid grid-cols-1 gap-6 px-8 py-5 text-base-content">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
      <div v-for="(stat, index) in stats" :key="index" class="card shadow-lg bg-base-100">
        <div class="card-body flex-row justify-center items-center">
          <Icon :glyph="stat.glyph" class="inline-block w-12 stroke-current mr-6" />
          <div>
            <h4 class="text-xl card-title mb-1">{{ stat.title }}</h4>
            <span class="text-lg font-bold">{{ stat.value }}{{ stat.text }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="card col-span-1 row-span-3 shadow-lg bg-base-100">
      <div class="card-body px-6">
        <div class="w-full flex items-center justify-evenly flex-col md:flex-row">
          <ul v-show="Object.keys(selectTags).length" class="flex flex-wrap justify-center tags-list w-full md:w-1/4">
            <li
              v-for="(tag, key) in selectTags"
              :key="key"
              @click="onRemoveTag(key)"
              class="badge badge-accent badge-outline cursor-pointer mb-1"
            >
              {{ tag }}
              <Icon glyph="x" class="inline-block w-4 h-4 ml-2 stroke-current" />
            </li>
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
              ref="searchBox"
              type="text"
              placeholder="Search"
              class="w-full pr-16 text-base input input-primary input-bordered"
            />
            <button @click="onSearch" :disabled="loading" class="absolute right-0 rounded-l-none btn btn-primary">
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
    <ColorPallet />
    <img alt="Vue logo" src="@/assets/logo.svg" />
  </div>
</template>

<script lang="ts">
import copy from 'copy-text-to-clipboard'
import { Options, Vue } from 'vue-class-component'
import Icon from '@/components/Icon.vue'
import Table from '@/components/Table.vue'
import Loader from '@/components/Loader.vue'
import ColorPallet from '@/components/ColorPallet.vue'

type Qnote = { id: number; date: Date; tags: string[]; url?: string; text?: string; code?: string }

@Options({
  components: {
    Icon,
    Table,
    Loader,
    ColorPallet,
  },
})
export default class Home extends Vue {
  loading = true
  stats = [
    { glyph: 'bookmark-alt', title: 'Total Qnotes', value: '103', text: '' },
    { glyph: 'tag', title: 'Total tags', value: '56', text: '' },
    { glyph: 'database', title: 'Database size', value: '10', text: 'ko' },
  ]
  tags = {
    1: 'telekinesis',
    2: 'time travel',
    3: 'invisibility',
  } as any
  tagPicker = '-1'
  selectTags = {} as any
  items: Qnote[] = [
    { id: 1, date: new Date(2021, 3, 10), tags: ['url', 'UI-Design', 'Creativity'], url: 'https://www.google.com/' },
    { id: 2, date: new Date(), tags: ['url', 'UI-Design', 'Creativity'], url: 'https://www.google.com/' },
    { id: 3, date: new Date(2021, 3, 9), tags: ['url', 'UI-Design', 'Creativity'], text: 'Hello world' },
    { id: 4, date: new Date(), tags: ['url', 'UI-Design', 'Creativity'], code: 'test' },
    { id: 5, date: new Date(2021, 3, 11), tags: ['url', 'UI-Design', 'Creativity'], url: 'https://www.google.com/' },
  ]

  beforeMount() {
    this.$watch('tagPicker', this.onTagPicker)
    this.fetchQnotes()
  }

  onTagPicker(newVal: string, oldVal: string) {
    if (newVal === '-1') return
    this.tagPicker = '-1'
    this.selectTags[newVal] = this.tags[newVal]
    delete this.tags[newVal]
  }

  onRemoveTag(id: string) {
    this.tags[id] = this.selectTags[id]
    delete this.selectTags[id]
  }

  onSearch() {
    console.log((this.$refs.searchBox as HTMLInputElement).value, Object.keys(this.selectTags))
    this.fetchQnotes()
  }

  onRowClick(row: Qnote) {
    const dataToCopy = row.url || row.text || row.code
    if (dataToCopy) {
      copy(String(dataToCopy))
      this.notify('Copied !', { type: 'info', queue: false, duration: 1500 })
    }
  }

  async fetchQnotes() {
    const query = new Promise((resolve) => {
      setTimeout(() => resolve(1), 1500)
    })
    this.loading = true
    try {
      await query
    } catch (err) {
      this.items = []
      this.notify(err, { title: 'Error', type: 'error', duration: 2500 })
    }
    this.loading = false
  }

  notify(
    text: string,
    options: { duration?: number; title?: string; type?: 'basic' | 'info' | 'success' | 'warning' | 'error'; queue?: boolean }
  ) {
    ;(this as any).$notify.show(text, options)
  }
}
</script>

<style scoped>
.tags-list li:not(:last-child) {
  margin-right: 0.25rem;
}
</style>
