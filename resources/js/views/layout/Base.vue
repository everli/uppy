<template>
  <main class="flex-1 flex flex-col overflow-x-hidden overflow-y-auto bg-gray-200 h-screen">
    <Nav></Nav>
    <div class="container mx-auto px-6 py-8">
      <slot/>
    </div>
  </main>
</template>

<script>
import Nav from '../../components/Nav';

export default {
  name: 'Base',
  components: {Nav}
}
</script>

<style scoped>

</style>
