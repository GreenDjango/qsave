<template>
  <div class="flex flex-col">
    <label class="label">
      <span class="label-text">URL</span>
    </label>

    <input
      type="url"
      placeholder="https://google.fr"
      class="input input-primary input-bordered bg-neutral"
      :class="{ 'input-warning': !isURLFriendly }"
      :disabled="disabled"
      :required="required"
      :value="modelValue"
      @input="emitValue($event)"
    />

    <label v-show="!isURLFriendly" class="label">
      <span class="label-text-alt">Need to be a URL with 'https://' or 'http://'.</span>
    </label>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'

@Options({
  components: {},
  emits: ['update:modelValue', 'update:isURLFriendly'],
  props: {
    disabled: Boolean,
    required: Boolean,
    modelValue: String,
    modelModifiers: {
      default: () => ({}),
    },
  },
  watch: {
    isURLFriendly(newValue) {
      this.$emit('update:isURLFriendly', newValue)
    },
  },
})
export default class UrlInput extends Vue {
  modelModifiers = {} as any
  modelValue = ''
  url = ''

  get isURLFriendly() {
    if (!this.modelValue) return true
    return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=/?&]{1,256}$/m.test(this.modelValue)
  }

  emitValue(evt: Event) {
    let val = (<any>evt.target)?.value
    if (this.modelModifiers['trim']) {
      val = val.trim()
    }
    this.$emit(`update:modelValue`, val)
  }
}
</script>
