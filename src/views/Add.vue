<template>
  <div class="home grid grid-cols-1 gap-6 px-8 py-5 max-w-screen-2xl mx-auto">
    <div class="card col-span-1 row-span-3 shadow-lg bg-base-100">
      <div class="card-body px-6">
        <h2 class="my-4 text-2xl font-bold card-title">Add a Qnote</h2>

        <div class="w-full flex items-center justify-evenly flex-col md:flex-row">
          <div v-show="!selectTags.length" class="flex justify-center w-full md:w-1/4">No tags selected</div>
          <ul v-show="!!selectTags.length" class="flex flex-wrap justify-center tags-list w-full md:w-1/4">
            <Badge
              v-for="tag in selectTags"
              :key="tag"
              :toHash="tag"
              :text="tag"
              :cross="true"
              @click="$refs.tagPicker.onRemoveTag(tag)"
              class="badge-outline cursor-pointer mb-1 mr-1"
            />
          </ul>

          <TagPicker
            ref="tagPicker"
            class="w-full sm:w-2/3 md:w-1/3 mt-3 md:mt-0"
            @update:select-tags="(n) => (selectTags = n)"
          />
        </div>

        <div class="form-control w-full">
          <UrlInput v-model.trim="url" @update:isURLFriendly="(n) => (isURLFriendly = n)" class="w-full" />

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

            <CodeInput v-model.trim="code" v-model:langPicker="langPicker" :disabled="!!text.length" class="flex-grow" />
          </div>

          <button
            class="btn btn-primary w-max place-self-end mt-4"
            :class="{ loading: loading }"
            :disabled="!canSubmit"
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
import { defineComponent } from 'vue'
import { mapStores, useQnotes, useAuth, usePopup } from '@/store'
import { QnotePartial } from '@/api/server.api'
import { notify } from '@/plugin/notify'
import { stringifyError, errorTitle } from '@/utils'
import Icon from '@/components/atoms/Icon.vue'
import Badge from '@/components/atoms/Badge.vue'
import TagPicker from '@/components/molecules/TagPicker.vue'
import UrlInput from '@/components/molecules/UrlInput.vue'
import CodeInput from '@/components/molecules/CodeInput.vue'

export default defineComponent({
  components: {
    Icon,
    Badge,
    TagPicker,
    UrlInput,
    CodeInput,
  },
  data: () => {
    return {
      loading: false,
      isURLFriendly: true,
      url: '',
      text: '',
      code: '',
      selectTags: [] as string[],
      langPicker: 'bash',
    }
  },
  computed: {
    qnotesStore() {
      return mapStores(useQnotes).qnotesStore()
    },
    authStore() {
      return mapStores(useAuth).authStore()
    },
    popupStore() {
      return mapStores(usePopup).popupStore()
    },

    canSubmit() {
      // const can = (this.url || this.code || this.text) && !this.loading && this.isURLFriendly
      return this.url
    },
  },
  methods: {
    clearInputs() {
      this.url = ''
      this.text = ''
      this.code = ''
      this.langPicker = 'bash'
    },

    async createQnote() {
      if (!this.url && !this.code && !this.text) {
        notify.show('Please complete at least one field', { title: 'Warning', type: 'warning', duration: 4000 })
        return
      }

      if (!this.authStore.apiKey) {
        this.popupStore.$patch({ hasNeedCredential: true })
        return
      }

      const qnote: QnotePartial = { tags: [] }
      if (this.url) qnote.url = this.url
      if (this.text) qnote.text = this.text
      else if (this.code) {
        qnote.code = this.code
        qnote.code_lang = this.langPicker
      }
      if (this.selectTags.length) qnote.tags = this.selectTags.sort()

      this.loading = true
      try {
        await this.qnotesStore.createQnote(qnote)
        notify.show('New qnote added', { title: 'Success', type: 'success', duration: 3000 })
        this.clearInputs()
        ;(<any>this.$refs).tagPicker.setSelectTags()
        ;(<any>this.$refs).tagPicker.fetchTags()
      } catch (err) {
        notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 4000 })
      }
      this.loading = false
    },
  },
})
</script>

<style scoped>
.btn-primary.btn-disabled,
.btn-primary.btn[disabled] {
  --tw-bg-opacity: 0.4;
  background-color: hsla(var(--p) / var(--tw-bg-opacity, 1));
  color: hsla(var(--pc) / var(--tw-text-opacity, 1));
}
</style>
