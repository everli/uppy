<template>
</template>

<script>
export default {
  name: 'DownloadRedirect',
  created() {
    this.axios.get('/applications/' + this.$route.params.slug).then((response) => {
      window.location.href = response.request.responseURL;
    }).catch(() => {
      this.$router.push({
        name: 'notFound'
      })
    })
  }
}
</script>

<style scoped>

</style>
