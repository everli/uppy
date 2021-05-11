<template>
  <Base>
    <div class="flex flex-wrap gap-x-3 justify-between">
      <h3 class="text-gray-800 text-3xl font-thin text-gray-200">
        <router-link :to="{name: 'application.index'}"
                     class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center mr-2">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </router-link>
        {{ app.name }}
      </h3>
      <div class="text-base mt-2">
        <router-link
            :to="{name: 'application.build.upload', params: {id: this.$route.params.id}}"
            class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center">
          <svg class="fill-current h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
               fill="currentColor">
            <path fill-rule="evenodd"
                  d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"/>
          </svg>
          <span>Upload</span>
        </router-link>
      </div>
    </div>

    <div class="mt-6 flex flex-wrap -mb-4">
      <PlatformCard v-for="(platformBuilds, platformName) in builds[this.$route.params.id]" :key="platformName"
                    :builds="platformBuilds"
                    :platform="platformName"/>
    </div>
  </Base>
</template>

<script>
import Base from '../layout/Base';
import PlatformCard from '../components/PlatformCard';
import Upload from "./Upload";
import {mapState} from "vuex";

export default {
  name: 'ApplicationBuilds',
  components: {PlatformCard, Upload, Base},
  created() {
    if (_.isEmpty(this.$store.state.application.applications)) {
      this.$store.dispatch('application/getApplicationById', this.$route.params.id).then(() =>
          this.$store.dispatch('application/setCurrentAppById', this.$route.params.id)
      );
    }
    if (_.isEmpty(this.builds[this.$route.params.id])) {
      this.$store.dispatch('build/getBuildsByApplication', this.$route.params.id);
    }
  },
  computed: {
    ...mapState({
      app: state => state.application.currentApp ?? {'name': null},
      builds: state => state.build.builds
    })
  }
}
</script>

<style scoped>

</style>
