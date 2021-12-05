<template>
  <div v-show="show" class="fixed z-10 top-0 bottom-0 left-0 right-0 flex flex-row items-center justify-center">
    <form @submit.prevent="" style="min-width: 18rem" class="card shadow-lg z-10 items-center bg-base-100 p-6">
      <div class="mb-3 text-2xl text-base-content">{{ title }}</div>
      <div class="mb-6">
        <slot />
      </div>
      <div class="flex flex-row justify-center gap-2 w-full" :class="{ 'flex-col-reverse': fullWidthBtn }">
        <button style="max-width: 22rem" class="btn flex-1" v-if="refuseText" @click="onRefuse">{{ refuseText }}</button>
        <button style="max-width: 22rem" class="btn btn-primary flex-1" primary @click="onValid">
          {{ validText || 'Got it' }}
        </button>
      </div>
    </form>
    <div class="absolute z-0 w-full h-full bg-black opacity-30"></div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'

@Options({
  emits: ['valid', 'refuse', 'close'],
  props: {
    title: String,
    validText: String,
    refuseText: String,
    showOnStart: Boolean,
    fullWidthBtn: Boolean,
  },
})
export default class Dialog extends Vue {
  title = ''
  validText = ''
  refuseText = ''
  showOnStart = false
  fullWidthBtn = false
  show = false

  beforeMount() {
    this.show = this.showOnStart ? true : false
  }

  onValid(ev: Event) {
    this.onClose(ev, true)
    this.$emit('valid', ev)
  }

  onRefuse(ev: Event) {
    this.onClose(ev)
    this.$emit('refuse', ev)
  }

  onClose(ev: Event, valid = false) {
    this.show = false
    this.$emit('close', ev, valid)
  }

  showDialog(show: boolean) {
    if (show === false) this.show = false
    else this.show = true
  }
}
</script>
