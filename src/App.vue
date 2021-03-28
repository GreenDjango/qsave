<template>
  <div id="app-core" class="bg-base-100">
    <!--
    <notifications />
    -->
    <Header class="w-full z-10" />
    <main class="text-content-900">
      <router-view />
    </main>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component'
import Header from '@/components/Header.vue'

@Options({
  components: { Header },
})
export default class App extends Vue {}
</script>

<style>
html,
body,
#app,
#app-core {
  height: 100%;
}

#app-core {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  scrollbar-width: thin;
  scrollbar-color: hsl(var(--c4, 174 60% 41%)) hsl(var(--c2, 174 60% 51%));
  scroll-behavior: smooth;
}

main {
  overflow: auto;
}

/* 
 #nav a.router-link-exact-active {
   color: #42b983;
 }
*/
</style>
