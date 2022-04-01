<template>
  <div class="about hero h-full">
    <div class="text-center hero-content">
      <div class="max-w-md">
        <h1 class="mb-5 text-4xl font-bold">Qsave</h1>
        <p class="mb-5 text-lg">Save your links and code samples in 1sec!</p>
        <router-link :to="{ name: 'Add' }" class="btn btn-primary"> Add a Qnote </router-link>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({})
</script>
