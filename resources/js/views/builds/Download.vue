<template>
  <div class="h-full min-h-screen mx-auto max-w-2xl flex flex-col">
    <div class="flex-grow-0 px-4 space-y-3">
      <div class="flex grid-cols-3 my-3 space-x-4">
        <div class="md:w-1/6 w-2/6">
          <img class="object-contain w-full rounded-md shadow-lg shadow-sm" :src="app.icon" :alt="app.name"/>
        </div>
        <div class="md:w-5/6 w-4/6 flex content-center flex-wrap truncate">
          <p class="w-full md:text-3xl mt-1 text-xl" v-text="app.name"></p>
          <p class="w-full lowercase text-gray-700 text-sm tracking-widest font-semibold">
            v{{ app.version }}
          </p>
          <p class="w-full text-gray-600 font-semibold text-xs tracking-widest font-light" v-text="app.date"></p>
        </div>
      </div>
      <a role="button" :href="app.download_url" @click="onDownloadStart"
         :class="{'opacity-50 pointer-events-none': downloading}"
         class="py-3 flex w-full rounded bg-primary-800 text-white hover:bg-primary-800 transition-none">
        <div class="flex mx-auto">
          <svg v-if="!downloading" class="w-6 h-6 -ml-1 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          <svg v-if="downloading" class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span class="uppercase">download</span>
        </div>
      </a>
    </div>

    <div class="flex-grow py-4 px-6" v-if="changelog!=null">
      <div class="my-2 text-gray-600 text-sm font-semibold select-none cursor-pointer flex justify-between">
        {{ $t('changelog') }}
      </div>
      <div v-html="markdown(changelog.content)" class="mt-6 space-y-3 text-sm prose max-w-full"/>
    </div>
    <div class="flex-grow-0 text-center text-xs py-3 font-semibold">
      <p class="text-primary-800">&copy; {{ app.organization }} {{ new Date().getFullYear() }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Download',
  data() {
    return {
      app: {},
      changelog: null,
      downloading: false,
    }
  },
  created() {
    this.axios.get('/api/v1/applications/' + this.$route.params.slug + '/' + this.$route.params.platform + '/latest')
        .then((response) => {
          this.app = response.data.data;

          this.changelog = this.app.changelogs.find((changelog) => this.$root.$i18n.locale.includes(changelog.locale));

          if (this.changelog == null) {
            this.changelog = this.app.changelogs.find((changelog) => this.$root.$i18n.fallbackLocale.includes(changelog.locale));
          }
        })
        .catch(() => {
          this.$router.push({
            name: 'notFound'
          })
        });
  },
  methods: {
    markdown(value) {
      return value ? marked(value) : value
    },
    onDownloadStart() {
      this.downloading = true;
      setTimeout(() => this.downloading = false, 10000);
    },
  }
}
</script>

<style scoped>

</style>
