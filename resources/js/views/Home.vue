<template>
  <div class="flex items-center justify-center h-screen">
    <div class="relative bg-primary-800 font-thin text-6xl text-white rounded-lg p-10">
      {{ $store.state.config.app_name }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'Home',
}
</script>

<style scoped>
</style>
