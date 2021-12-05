<template>
  <div :class="{ 'alert-box-down': isDown }" class="alert-box absolute z-20 flex flex-col p-2 cursor-pointer">
    <div class="alert text-neutral-content">
      <div class="flex-1">
        <div class="mr-4">
          <Icon v-if="type == 'info'" glyph="information-circle" class="flex-shrink-0 w-6 h-6" color="#2094F3" />
          <Icon v-else-if="type == 'success'" glyph="check-circle" class="flex-shrink-0 w-6 h-6" color="#009485" />
          <Icon v-else-if="type == 'warning'" glyph="exclamation" class="flex-shrink-0 w-6 h-6" color="#FF9900" />
          <Icon v-else-if="type == 'error'" glyph="exclamation-circle" class="flex-shrink-0 w-6 h-6" color="#ff5722" />
          <Icon v-else glyph="bell" class="flex-shrink-0 w-6 h-6" color="#009688" />
        </div>
        <label>
          <h4 class="text-lg leading-6">{{ title }}</h4>
          <p class="text-sm pt-1">{{ text }}</p>
        </label>
      </div>
      <div class="flex-none ml-4">
        <button @click="onItemClick" class="btn btn-sm btn-ghost btn-square" aria-label="close alert">
          <Icon glyph="x" class="w-6 h-6 stroke-current" />
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Events from './events.js'
import Icon from '@/components/atoms/Icon.vue'

export default {
  name: 'Notify',
  components: { Icon },
  data() {
    return {
      title: '',
      text: '',
      type: '',
      list: [],
      isDown: false,
      duration: 3000,
    }
  },
  mounted() {
    Events.$on('show', this.addItem)
    Events.$on('clear', this.clear)
  },
  methods: {
    showAlert() {
      if (!this.list[0] || this.isDown) return
      const item = this.list[0]

      this.title = item.title || ''
      this.text = item.text || ''
      this.type = item.type || ''
      this.isDown = true
      if (item.duration >= 0) {
        item.timer = setTimeout(() => {
          this.onDestroy(item)
        }, item.duration)
      }
    },
    addItem(event) {
      const duration = typeof event.duration === 'number' ? event.duration : this.duration
      const { title, text, type, queue } = event
      const item = { title, text, type, duration }

      if (queue === false) this.clear()
      this.list.push(item)
      this.showAlert()
    },
    clear() {
      for (const item of this.list) {
        if (item.timer) clearTimeout(item.timer)
      }
      this.isDown = false
      this.list = []
    },
    onDestroy(item) {
      if (item.timer) clearTimeout(item.timer)
      this.list.shift()
      this.isDown = false
      this.showAlert()
    },
    onItemClick() {
      if (!this.list[0]) return
      this.onDestroy(this.list[0])
    },
  },
}
</script>

<style scoped>
.alert-box {
  position: fixed;
  top: 0;
  left: 50%;
  transform: translate(-50%, -101%);
  transition: transform 700ms cubic-bezier(0.32, 0, 0.07, 1);
}

.alert-box-down {
  transform: translate(-50%, 0);
}
</style>
