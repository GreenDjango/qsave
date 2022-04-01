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
import { defineComponent } from 'vue'

export default defineComponent({
  props: {
    disabled: Boolean,
    required: Boolean,
    modelValue: {
      type: String,
      default: '',
    },
    modelModifiers: {
      type: Object,
      default: {},
    },
  },
  emits: {
    'update:modelValue': (payload: string) => true,
    'update:isURLFriendly': (payload: boolean) => true,
  },
  watch: {
    isURLFriendly(newValue: boolean) {
      this.$emit('update:isURLFriendly', newValue)
    },
  },
  computed: {
    isURLFriendly() {
      if (!this.modelValue) return true
      return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=/?&]{1,256}$/m.test(this.modelValue)
    },
  },
  methods: {
    emitValue(evt: Event) {
      let val = (<any>evt.target)?.value
      if (this.modelModifiers['trim']) {
        val = val.trim()
      }
      this.$emit(`update:modelValue`, val)
    },
  },
})
</script>
