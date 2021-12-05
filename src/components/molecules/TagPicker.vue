<template>
  <div class="relative">
    <label class="label">
      <span class="label-text">Tags</span>
    </label>
    <input
      type="text"
      placeholder="Tags"
      class="input input-primary input-bordered bg-neutral w-full"
      :class="{ 'input-warning': !isSearchTagFriendly }"
      v-model.trim="searchTag"
      @focus="onSearchTagFocus(true)"
      @blur="onSearchTagFocus(false)"
      @keypress.enter="onChoseNewTag"
    />

    <div
      v-show="searchTagFocus && matchTags.length"
      class="absolute mt-1 z-10 card cursor-pointer bg-neutral border border-neutral-focus rounded-none w-full"
    >
      <div class="max-h-80 overflow-y-auto">
        <li
          v-for="(tag, idx) in matchTags"
          :key="tag"
          @mousedown.prevent="onChoseExistingTag(tag)"
          class="px-4 py-1 whitespace-nowrap overflow-hidden overflow-ellipsis border-accent-focus border-opacity-70 hover:bg-accent-focus hover:bg-opacity-70"
          :class="{ 'border-t': idx !== 0 }"
        >
          {{ tag }}
        </li>
      </div>
    </div>

    <label v-show="!isSearchTagFriendly" class="label">
      <span class="label-text-alt">Tag can only contain a-z or 0-9 or - digit.</span>
    </label>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import { mapStores, useTags } from '@/store'
import { notify } from '@/plugin/notify'
import { stringifyError, errorTitle } from '@/utils'

@Options({
  components: {},
  props: {},
  emits: ['update:select-tags'],
  watch: {
    selectTags(newValue) {
      this.$emit('update:select-tags', newValue)
    },
  },
})
export default class TagPicker extends Vue {
  searchTag = ''
  searchTagFocus = false
  tags = [] as { name: string; select: boolean; local: boolean }[]

  get tagsStore() {
    return mapStores(useTags).tagsStore()
  }

  beforeMount() {
    this.fetchTags()
  }

  // Search Tag
  get isSearchTagFriendly() {
    return !/[^a-z0-9-]/.test(this.searchTag)
  }

  get matchTags() {
    const reg = new RegExp(this.searchTag, 'i')
    let match = []
    for (const tag of this.tags) {
      if (!tag.select && reg.test(tag.name)) match.push(tag.name)
    }
    return match.sort()
  }

  get selectTags() {
    return this.tags
      .filter((val) => val.select)
      .map((val) => val.name)
      .sort()
  }

  onSearchTagFocus(isFocus: boolean) {
    this.searchTagFocus = isFocus
  }

  onChoseNewTag() {
    if (!this.isSearchTagFriendly) return
    this.onChoseExistingTag(this.searchTag)
  }

  onChoseExistingTag(tag: string) {
    this.searchTag = ''
    const findTag = this.tags.find((val) => val.name === tag)
    if (findTag) findTag.select = true
    else this.tags.push({ name: tag, select: true, local: true })
  }

  onRemoveTag(tag: string) {
    const findTag = this.tags.find((val) => val.name === tag)
    if (findTag) findTag.select = false
    else this.tags.push({ name: tag, select: false, local: true })
  }

  setTags(...tags: string[]) {
    const locals = this.tags.filter((val) => val.local)
    this.tags = [...new Set(tags)].map((tag) => {
      return {
        name: tag,
        select: this.tags.some((val) => val.name === tag && val.select),
        local: false,
      }
    })
    for (const local of locals) {
      const findTag = this.tags.find((val) => val.name === local.name)
      if (!findTag) this.tags.push(local)
    }
  }

  setSelectTags(...tags: string[]) {
    this.tags.forEach((val) => (val.select = false))
    for (const tag of tags) {
      const findTag = this.tags.find((val) => val.name === tag)
      if (findTag) findTag.select = true
      else this.tags.push({ name: tag, select: true, local: true })
    }
  }

  // Fetch data
  async fetchTags() {
    try {
      await this.tagsStore.fetchStats()
      const tags = Object.keys(this.tagsStore.tags || {})
      this.setTags(...tags)
    } catch (err) {
      notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 3000 })
    }
  }
}
</script>
