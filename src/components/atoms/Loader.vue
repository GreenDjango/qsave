<template>
  <div class="flex justify-center items-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
      />
    </svg>
  </div>
</template>

<script lang="ts">
import { Vue } from 'vue-class-component'

export default class Loader extends Vue {}
</script>

<style scoped>
svg {
  animation: 2s linear 0s infinite loader;
}

@keyframes loader {
  0% {
    stroke: hsl(var(--af));
    transform: rotateZ(0);
  }
  50% {
    stroke: hsl(var(--a));
  }
  100% {
    stroke: hsl(var(--af));
    transform: rotateZ(-360deg);
  }
}
</style>
