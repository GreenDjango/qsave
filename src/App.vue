<template>
  <notifications />
  <GlobalDialogs />
  <router-link
    :to="{ name: 'Add' }"
    class="absolute right-4 bottom-4 btn btn-primary btn-active btn-circle z-10"
    aria-label="new qnote"
  >
    <Icon glyph="plus" class="inline-block w-7 stroke-current" />
  </router-link>
  <AppHeader class="w-full z-10" />
  <main class="text-base-content">
    <router-view />
  </main>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import Icon from '@/components/atoms/Icon.vue'
import GlobalDialogs from '@/components/dedicated/GlobalDialogs.vue'
import AppHeader from '@/components/dedicated/Header.vue'
import Notify from '@/plugin/notify/Notify.vue'

export default defineComponent({
  components: { AppHeader, Icon, GlobalDialogs, notifications: Notify },
})
</script>

<style>
html,
body,
#app {
  height: 100%;
}

#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  scrollbar-width: thin;
  scrollbar-color: hsl(var(--pf, 174 60% 41%)) hsl(var(--b2, 174 60% 51%));
  scroll-behavior: smooth;
}

main {
  overflow: auto;
  height: calc(100% - 64px);
}

/* 
 #nav a.router-link-exact-active {
   color: #42b983;
 }
*/
</style>
