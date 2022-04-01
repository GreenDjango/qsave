<template>
  <div>
    <label class="label pt-0">
      <span class="label-text self-end">Your code</span>
      <select
        :value="langPicker"
        @change="$emit(`update:langPicker`, $event.target.value)"
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
import { defineComponent, PropType } from 'vue'
import Prismjs from 'prismjs'
import MockupCode from '@/components/molecules/MockupCode.vue'

export default defineComponent({
  components: {
    MockupCode,
  },
  props: {
    disabled: Boolean,
    required: Boolean,
    langPicker: {
      type: String,
      default: 'bash',
    },
    modelValue: String,
    modelModifiers: {
      type: Object as PropType<{ [key: string]: boolean }>,
      default: {},
    },
  },

  data() {
    return {
      languages: Object.keys(Prismjs.languages).sort(),
    }
  },
  emits: {
    'update:modelValue': (payload: Event) => true,
    'update:langPicker': (payload: Event) => true,
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
