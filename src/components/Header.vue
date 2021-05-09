<template>
  <div class="navbar shadow-lg bg-neutral-focus text-neutral-content">
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
        <router-link :to="{ name: 'Home' }" class="btn btn-ghost btn-sm">
          <Icon glyph="home" class="inline-block w-5 mr-2 stroke-current" />
          Home
        </router-link>
        <router-link :to="{ name: 'Add' }" class="btn btn-ghost btn-sm">
          <Icon glyph="plus-circle" class="inline-block w-5 mr-2 stroke-current" />
          Add a Qnote
        </router-link>
        <router-link :to="{ name: 'About' }" class="btn btn-ghost btn-sm">
          <Icon glyph="information-circle" class="inline-block w-5 mr-2 stroke-current" />
          About
        </router-link>
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

<style scoped>
/* 
 #nav a.router-link-exact-active {
   color: #42b983;
 }
*/

.router-link-active {
  background-color: hsla(var(--bc, 215 28% 17%) / 0.2);
}
</style>
