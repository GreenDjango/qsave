<template>
  <div>
    <label class="label pt-0">
      <span class="label-text self-end">Your code</span>
      <select
        :value="langPicker"
        @input="$emit(`update:langPicker`, $event)"
        v-model="langPicker"
        :disabled="disabled"
        class="select select-bordered select-sm pr-7 label-text-alt"
      >
        <option disabled="" selected="" value="-1">Choose a language</option>
        <option v-for="lang in languages" :key="lang" :value="lang">
          {{ lang }}
        </option>
      </select>
    </label>
    <div class="relative">
      <textarea
        class="textarea textarea-primary textarea-bordered h-32 bg-neutral w-full"
        :disabled="disabled"
        :required="required"
        placeholder="Code"
        :value="modelValue"
        @input="emitValue($event)"
      ></textarea>
      <MockupCode v-show="modelValue.length" :language="langPicker" :code="modelValue" :plugins="['line-numbers']" class="mt-2" />
    </div>
  </div>
</template>

<script lang="ts">
import Prismjs from 'prismjs'
import { Options, Vue } from 'vue-class-component'
import MockupCode from '@/components/molecules/MockupCode.vue'

@Options({
  components: { MockupCode },
  emits: ['update:modelValue', 'update:langPicker'],
  props: {
    disabled: Boolean,
    required: Boolean,
    langPicker: String,
    modelValue: String,
    modelModifiers: {
      default: () => ({}),
    },
  },
})
export default class UrlInput extends Vue {
  modelModifiers = {} as any
  languages = Object.keys(Prismjs.languages).sort()
  langPicker = 'bash'

  emitValue(evt: Event) {
    let val = (<any>evt.target)?.value
    if (this.modelModifiers['trim']) {
      val = val.trim()
    }
    this.$emit(`update:modelValue`, val)
  }
}
</script>
