<template>
  <div class="dialogs">
    <DialogModal ref="dialog401" title="Unauthorized Request" validText="Login" refuseText="Continue" @close="close401">
      <div class="text-center text-md text-base-content opacity-80">
        You made an unauthorized request (HTTP 401).<br />
        You can continue to navigate<br />
        or try to re login again.
      </div>
    </DialogModal>
    <DialogModal ref="dialogAuth" title="Authentication" validText="Login" refuseText="Back" @close="closeAuth">
      <div class="text-center text-md text-base-content opacity-80">
        <label class="label"> You need to add your secret key for upload new Qnote. </label>
        <input
          type="password"
          placeholder="XXX"
          min="6"
          class="input input-primary input-bordered bg-neutral mt-2 w-full"
          v-model.trim="authKey"
        />
      </div>
    </DialogModal>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import { mapStores, usePopup, useAuth } from '@/store'
import DialogModal from '@/components/organisms/Dialog.vue'

export default defineComponent({
  components: { DialogModal },
  data() {
    return {
      authKey: '',
    }
  },
  watch: {
    'popupStore.is401': function (value) {
      if (value) (this.$refs.dialog401 as any).showDialog()
    },
    'popupStore.hasNeedCredential': function (value) {
      if (value) (this.$refs.dialogAuth as any).showDialog()
    },
  },
  computed: {
    popupStore() {
      return mapStores(usePopup).popupStore()
    },

    authStore() {
      return mapStores(useAuth).authStore()
    },
  },
  methods: {
    close401(_ev: Event, hasValid: boolean) {
      this.popupStore.$patch({ is401: false })
      if (!hasValid) return
      this.popupStore.$patch({ hasNeedCredential: true })
    },

    closeAuth(_ev: Event, hasValid: boolean) {
      this.popupStore.$patch({ hasNeedCredential: false })
      if (!hasValid || !this.authKey) return
      this.authStore.$patch({ apiKey: this.authKey })
      this.authKey = ''
    },
  },
})
</script>
