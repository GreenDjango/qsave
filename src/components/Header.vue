<template>
  <!-- 
        <Icon glyph="color-swatch" class="inline-block w-6 h-6 stroke-current" />
        <button class="btn btn-square btn-ghost"></button>

 -->
  <div class="navbar mb-2 shadow-lg bg-neutral text-neutral-content rounded-b-2xl">
    <div class="flex-none">
      <button v-on:click="reloadPage" class="btn btn-square btn-ghost">
        <img src="../assets/logo.svg" class="inline-block w-10 h-10" />
      </button>
    </div>
    <div class="flex-1 px-2 mx-2">
      <span class="text-lg font-bold"> Qsave </span>
    </div>
    <div class="flex-none flex px-2 mx-2">
      <div class="flex items-stretch">
        <a class="btn btn-ghost btn-sm">
          <Icon glyph="truck" class="inline-block w-6 h-6 stroke-current" />
          Likes
        </a>
        <a class="btn btn-ghost btn-sm">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 mr-2 stroke-current">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            ></path>
          </svg>
          Notifications
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import Icon from '@/components/Icon.vue'

@Options({
  components: { Icon },
})
export default class Header extends Vue {
  themes = ['light', 'dark', 'valentine', 'retro', 'synthwave', 'cyberpunk', 'black', 'dracula', 'garden']
  themeIndex = 0

  reloadPage() {
    window.location.replace('/')
  }
}
</script>
