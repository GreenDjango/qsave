<!-- 
  https://github.com/tailwindlabs/heroicons
  A set of free MIT-licensed high-quality SVG icons
-->
<template>
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" :stroke="color">
    <path
      v-for="(item, index) in icon.data"
      :key="index"
      :stroke-linecap="item['stroke-linecap']"
      :stroke-linejoin="item['stroke-linejoin']"
      :stroke-width="item['stroke-width']"
      :d="item.d"
    />
  </svg>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import heroicons from '@/assets/heroicons'

export default defineComponent({
  props: {
    glyph: String,
    iconId: Number,
    color: String,
  },
  data() {
    return {
      icons: heroicons,
    }
  },
  computed: {
    icon() {
      let icon = undefined
      if (this.glyph) {
        icon = this.icons.find((val) => val.name === this.glyph)
      } else if (this.iconId && this.icons[this.iconId]) {
        icon = this.icons[this.iconId]
      }
      if (!icon) {
        console.warn(`Icon '${this.glyph || this.iconId}' not found.`)
        return this.icons[0]
      }
      return icon
    },
  },
})
</script>
