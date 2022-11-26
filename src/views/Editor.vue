<template>
  <div class="home grid grid-cols-1 gap-6 px-8 py-5 max-w-screen-2xl mx-auto">
    <DialogModal ref="confirmDelete" title="Confirm Delete" validText="Delete" refuseText="Cancel" @valid="deleteQnote">
      <div class="text-center text-md text-base-content opacity-80">Are you sure you want to delete this Qnote?</div>
    </DialogModal>
    <div class="card col-span-1 row-span-3 shadow-lg bg-base-100">
      <div class="card-body px-6">
        <h2 class="my-4 text-2xl font-bold card-title">Update a Qnote</h2>

        <div class="w-full flex items-center justify-evenly flex-col md:flex-row">
          <div v-show="!selectTags.length" class="flex justify-center w-full md:w-1/4">No tags selected</div>
          <ul v-show="!!selectTags.length" class="flex flex-wrap justify-center tags-list w-full md:w-1/4">
            <Badge
              v-for="tag in selectTags"
              :key="tag"
              :toHash="tag"
              :text="tag"
              :cross="true"
              @click=";($refs.tagPicker as any).onRemoveTag(tag)"
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
                :disabled="!!code.length && !text.length"
                placeholder="Text"
                v-model.trim="text"
              ></textarea>
            </div>

            <div class="divider md:divider-vertical opacity-70">OR</div>

            <CodeInput v-model.trim="code" v-model:langPicker="langPicker" :disabled="!!text.length" class="flex-grow" />
          </div>

          <div class="flex flex-row justify-between mt-4">
            <button class="btn w-max justify-self-start" @click="$router.back()">back</button>
            <div class="flex flex-row justify-end gap-4">
              <button class="btn w-max" :class="{ loading: loading }" :disabled="loading" @click="refreshQnote">
                reset change
              </button>
              <button
                class="btn btn-warning w-max"
                :class="{ loading: loading }"
                :disabled="loading"
                @click=";($refs.confirmDelete as any).showDialog()"
              >
                delete
              </button>
              <button class="btn btn-primary w-max" :class="{ loading: loading }" :disabled="!canSubmit" @click="updateQnote">
                submit
              </button>
            </div>
          </div>
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
import Badge from '@/components/atoms/Badge.vue'
import TagPicker from '@/components/molecules/TagPicker.vue'
import UrlInput from '@/components/molecules/UrlInput.vue'
import CodeInput from '@/components/molecules/CodeInput.vue'
import DialogModal from '@/components/organisms/Dialog.vue'

export default defineComponent({
  components: {
    Badge,
    TagPicker,
    UrlInput,
    CodeInput,
    DialogModal,
  },
  props: {
    propId: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      loading: true,
      isURLFriendly: true,
      url: '',
      text: '',
      code: '',
      selectTags: [] as string[],
      langPicker: 'bash',
    }
  },
  watch: {
    qnoteID(newValue) {
      this.fetchQnote(newValue)
    },
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

    qnoteID() {
      return Number(this.propId)
    },

    canSubmit() {
      return (this.url || this.code || this.text) && !this.loading && this.isURLFriendly
    },
  },
  beforeMount() {
    this.refreshQnote()
  },
  methods: {
    clearInputs() {
      this.url = ''
      this.text = ''
      this.code = ''
      this.langPicker = 'bash'
    },

    // Fetch data
    async refreshQnote() {
      await this.fetchQnote(this.qnoteID)
    },

    async fetchQnote(qnoteID: number) {
      this.loading = true
      try {
        const qnote = await this.qnotesStore.fetchQnote(qnoteID)
        if (!qnote) {
          const error = Error('Qnote not found.') as Error & { status?: number }
          error.status = 404
          throw error
        }

        if (qnote.url) this.url = qnote.url
        if (qnote.code) this.code = qnote.code
        if (qnote.text) this.text = qnote.text
        if (qnote.code_lang) this.langPicker = qnote.code_lang
        ;(this.$refs as any).tagPicker.setSelectTags(...(qnote.tags || []))
        ;(this.$refs as any).tagPicker.fetchTags()
      } catch (err: any) {
        notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 3000 })
        if (err.status === 404 || err.response.status === 404) {
          this.$router.replace({
            name: 'NotFound',
            params: { catchAll: this.$route.path.slice(1) },
            query: this.$route.query,
            hash: this.$route.hash,
            path: this.$route.path,
          })
        }
      }
      this.loading = false
    },

    async updateQnote() {
      if (!this.url && !this.code && !this.text) {
        notify.show('Please complete at least one field', { title: 'Warning', type: 'warning', duration: 4000 })
        return
      }

      if (!this.authStore.apiKey) {
        this.popupStore.$patch({ hasNeedCredential: true })
        return
      }

      const qnote: QnotePartial = {
        tags: this.selectTags.sort(),
        url: this.url,
        text: this.text,
        code: this.code,
        code_lang: this.langPicker,
      }

      this.loading = true
      try {
        await this.qnotesStore.updateQnote(this.qnoteID, qnote)
        notify.show('Qnote updated', { title: 'Success', type: 'success', duration: 3000 })
        this.clearInputs()
        await this.refreshQnote()
      } catch (err) {
        notify.show(stringifyError(err), { title: errorTitle(err), type: 'error', duration: 4000 })
      }
      this.loading = false
    },

    async deleteQnote() {
      if (!this.authStore.apiKey) {
        this.popupStore.$patch({ hasNeedCredential: true })
        return
      }

      this.loading = true
      try {
        await this.qnotesStore.deleteQnote(this.qnoteID)
        notify.show('Qnote deleted', { title: 'Success', type: 'success', duration: 3000 })
        this.clearInputs()
        this.$router.replace({ name: 'Home' })
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

.btn-warning {
  --tw-bg-opacity: 0.7;
  --tw-border-opacity: 0.7;
}

.btn-warning:hover {
  --tw-bg-opacity: 0.6;
  --tw-border-opacity: 0.6;
}
</style>
