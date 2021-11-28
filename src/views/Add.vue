<template>
  <div class="home grid grid-cols-1 gap-6 px-8 py-5 max-w-screen-2xl mx-auto">
    <div class="card col-span-1 row-span-3 shadow-lg bg-base-100">
      <div class="card-body px-6">
        <h2 class="my-4 text-2xl font-bold card-title">Add a Qnote</h2>

        <div class="w-full flex items-center justify-evenly flex-col md:flex-row">
          <div v-show="!Object.keys(selectTags).length" class="flex justify-center w-full md:w-1/4">
            No tags selected
          </div>

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
              @keypress.enter="onNewChoseTag"
            />

            <div
              v-show="searchTagFocus && Object.keys(matchTags).length"
              class="absolute mt-1 z-10 card rounded-none cursor-pointer bg-neutral w-full"
            >
              <div class="max-h-80 overflow-y-auto">
                <li
                  v-for="(tag, key, idx) in matchTags"
                  :key="key"
                  @mousedown.prevent="onChoseTag(key)"
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
        </div>

        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">URL</span>
          </label>
          <input
            type="text"
            placeholder="https://google.fr"
            class="input input-primary input-bordered bg-neutral"
            :class="{ 'input-warning': !isURLFriendly }"
            v-model.trim="url"
          />
          <label v-show="!isURLFriendly" class="label">
            <span class="label-text-alt">Need to be a URL with 'https://' or 'http://'.</span>
          </label>

          <div class="flex flex-col w-full md:flex-row mt-5">
            <div class="flex-grow">
              <label class="label h-10 pt-0">
                <span class="label-text self-end">Your text</span>
              </label>
              <textarea
                class="textarea textarea-primary textarea-bordered h-32 bg-neutral w-full"
                :disabled="code.length"
                placeholder="Text"
                v-model.trim="text"
              ></textarea>
            </div>

            <div class="divider md:divider-vertical opacity-70">OR</div>

            <div class="flex-grow">
              <label class="label pt-0">
                <span class="label-text self-end">Your code</span>
                <select v-model="langPicker" :disabled="text.length" class="select select-bordered select-sm pr-7 label-text-alt">
                  <option disabled="" selected="" value="-1">Choose a language</option>
                  <option v-for="lang in languages" :key="lang" :value="lang">
                    {{ lang }}
                  </option>
                </select>
              </label>
              <div class="relative">
                <textarea
                  class="textarea textarea-primary textarea-bordered h-32 bg-neutral w-full"
                  :disabled="text.length"
                  placeholder="Code"
                  v-model.trim="code"
                ></textarea>
                <MockupCode v-show="code.length" :language="langPicker" :code="code" :plugins="['line-numbers']" class="mt-2" />
              </div>
            </div>
          </div>

          <button
            class="btn btn-primary w-max place-self-end mt-4"
            :class="{ loading: loading }"
            :disabled="!url && !code && !text"
            @click="createQnote"
          >
            submit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Prismjs from 'prismjs'
import { Options, Vue } from 'vue-class-component'
import { mapStores, useQnotes, useStats } from '@/store'
import { QnotePartial } from '@/api/server.api'
import { notify } from '@/plugin/notify'
import { stringifyError, errorTitle } from '@/utils'
import Icon from '@/components/Icon.vue'
import Badge from '@/components/Badge.vue'
import MockupCode from '@/components/MockupCode.vue'

@Options({
  components: {
    Icon,
    Badge,
    MockupCode,
  },
})
export default class Add extends Vue {
  loading = true
  searchTag = ''
  searchTagFocus = false
  url = ''
  text = ''
  code = ''
  tags = {} as { [s: string]: string }
  selectTags = {} as { [s: string]: string }
  languages = Object.keys(Prismjs.languages).sort()
  langPicker = 'bash'

  get qnotesStore() {
    return mapStores(useQnotes).qnotesStore()
  }

  get statsStore() {
    return mapStores(useStats).statsStore()
  }

  beforeMount() {
    this.fetchStats()
  }

  clearInputs() {
    this.url = ''
    this.text = ''
    this.code = ''
    this.langPicker = 'bash'
  }

  // Search Tag
  get isSearchTagFriendly() {
    return !/[^a-z0-9-]/.test(this.searchTag)
  }

  get isURLFriendly() {
    if (!this.url) return true
    return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=/]{1,256}$/m.test(this.url)
  }

  get matchTags() {
    const reg = new RegExp(this.searchTag, 'i')
    let match = {} as { [s: string]: string }
    for (const key in this.tags) {
      const value = this.tags[key]
      if (reg.test(value)) match[key] = value
    }
    return match
  }

  onSearchTagFocus(isFocus: boolean) {
    this.searchTagFocus = isFocus
  }

  onNewChoseTag() {
    if (!this.isSearchTagFriendly) return
    const newTag = this.searchTag
    let keyExist = null
    for (const key in this.tags) {
      if (newTag === this.tags[key]) keyExist = key
    }
    if (keyExist === null) {
      keyExist = `local${Date.now()}`
      this.tags[keyExist] = newTag
    }
    this.onChoseTag(keyExist)
  }

  onChoseTag(tagKey: string) {
    this.searchTag = ''
    this.selectTags[tagKey] = this.tags[tagKey]
    delete this.tags[tagKey]
  }

  onRemoveTag(id: string) {
    this.tags[id] = this.selectTags[id]
    delete this.selectTags[id]
  }

  resetTagPicker(newTags: string[]) {
    this.selectTags = {}
    this.tags = {}
    newTags.forEach((val, idx) => {
      this.tags[idx] = val
    })
  }

  // Fetch data
  async fetchStats() {
    this.loading = true
    try {
      await this.statsStore.fetchStats()
      const tags = Object.keys(this.statsStore.stats?.all_tags || {})
      this.resetTagPicker(tags.sort())
    } catch (err) {
      notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 3000 })
    }
    this.loading = false
  }

  async createQnote() {
    if (!this.url && !this.code && !this.text)
      notify.show('Please complete at least one field', { title: 'Warning', type: 'warning', duration: 4000 })

    const qnote: QnotePartial = { tags: '' }
    if (this.url) qnote.url = this.url
    if (this.text) qnote.text = this.text
    else if (this.code) {
      qnote.code = this.code
      qnote.code_lang = this.langPicker
    }
    const stringTags = Object.values(this.selectTags)
      .map((str) => str.trim())
      .filter((str) => str)
      .sort()
      .join(';')
    if (stringTags) qnote.tags = stringTags

    this.loading = true
    try {
      await this.qnotesStore.createQnote(qnote)
      notify.show('New qnote added', { title: 'Success', type: 'success', duration: 3000 })
      this.clearInputs()
      await this.fetchStats()
    } catch (err) {
      notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 4000 })
    }
    this.loading = false
  }
}
</script>

<style scoped>
.btn-disabled,
.btn[disabled] {
  --tw-bg-opacity: 0.4;
  background-color: hsla(var(--p) / var(--tw-bg-opacity, 1));
  color: hsla(var(--pc) / var(--tw-text-opacity, 1));
}
</style>
