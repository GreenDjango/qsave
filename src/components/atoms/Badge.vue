<template>
  <div class="badge" :class="colorClass">
    {{ text }}
    <Icon v-if="cross" glyph="x" class="inline-block w-4 h-4 ml-2 stroke-current" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import { sha256 } from 'hash.js'
import Icon from '@/components/atoms/Icon.vue'

export default defineComponent({
  components: {
    Icon,
  },
  props: {
    text: {
      type: String,
      default: '',
    },
    toHash: {
      type: String,
      default: '',
    },
    cross: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      colorIdx: '0',
    }
  },
  watch: {
    toHash: {
      handler() {
        this.selectColor()
      },
      immediate: true,
    },
  },
  computed: {
    colorClass() {
      return {
        [`badge-color-${this.colorIdx}`]: true,
      }
    },
  },
  methods: {
    selectColor() {
      this.colorIdx = sha256().update(this.toHash.toLowerCase()).digest('hex')[0]
    },
  },
})
</script>

<style scoped>
.badge-color-0,
.badge-color-1 {
  border-color: hsl(0 80% 65%);
}
.badge-color-2,
.badge-color-3 {
  border-color: hsl(0 80% 75%);
}
.badge-color-4,
.badge-color-5 {
  border-color: hsl(0 80% 85%);
}
.badge-color-6,
.badge-color-7 {
  border-color: hsl(97 46% 65%);
}
.badge-color-8,
.badge-color-9 {
  border-color: hsl(97 96% 85%);
}
.badge-color-a,
.badge-color-b {
  border-color: hsl(138 46% 65%);
}
.badge-color-c,
.badge-color-d {
  border-color: hsl(138 96% 85%);
}
.badge-color-e,
.badge-color-f {
  border-color: hsl(0 0% 100%);
}
</style>
