<template>
  <div class="hero min-h-full">
    <div class="text-center hero-content">
      <div class="max-w-md flex flex-col">
        <img class="mb-3 rounded p-8" :src="require('@/assets/404.png')" alt="404 logo" />
        <h1 class="mb-5 text-4xl font-bold">Page not found</h1>
        <p class="mb-5 text-lg">Oops! It seems like we can't find the page you are looking for.</p>
        <div class="flex flex-row justify-center gap-4">
          <button class="btn" @click="$router.back()">
            Go Back
            <Icon glyph="reply" class="w-5 ml-2 stroke-current" />
          </button>
          <router-link class="btn btn-primary" :to="{ name: 'Home', replace: true }">
            Go Home
            <Icon glyph="home" class="w-5 ml-2 stroke-current" />
          </router-link>
        </div>
        <div class="flex flex-wrap"></div>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '@/components/atoms/Icon.vue'

export default {
  components: {
    Icon,
  },
}
</script>

<style scoped>
img {
  background-color: rgba(250, 250, 250, 0.5);
}
</style>
