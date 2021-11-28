<template>
  <div class="hero min-h-full">
    <div class="text-center hero-content">
      <div class="max-w-md">
        <img class="mb-3 rounded p-8" :src="require('@/assets/404.png')" alt="404 logo" />
        <h1 class="mb-5 text-4xl font-bold">Page not found</h1>
        <p class="mb-5 text-lg">Oops! It seems like we can't find the page you are looking for.</p>
        <router-link class="btn btn-primary" :to="{ name: 'Home' }">Go Home</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
img {
  background-color: rgba(250, 250, 250, 0.5);
}
</style>
