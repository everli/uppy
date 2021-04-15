<template>
  <Base>
    <div class="flex flex-wrap gap-x-3 justify-between">
      <h3 class="text-secondary-800 text-3xl font-thin">
        Applications
      </h3>
      <div class="text-base mt-2">
        <router-link :to="{name: 'application.create'}" class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center">
          <svg class="fill-current h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          <span>New Application</span>
        </router-link>
      </div>
    </div>

    <div class="flex flex-wrap lg:-mx-4 xl:-mx-4">
      <ApplicationCard v-for="app in applications.data" :id="'app-'+app.id" :key="app.id" :app="app"/>
    </div>
  </Base>
</template>

<script>
import Base from '../layout/Base';
import ApplicationCard from '../../components/ApplicationCard';

export default {
  name: 'ApplicationIndex',
  components: {Base, ApplicationCard},
  data() {
    return {
      applications: [],
    }
  },
  created() {
    this.axios.get('/api/v1/applications').then((response) => {
      this.applications = response.data;
    })
  }
}
</script>

<style scoped>

</style>
