<template>
  <Base>
    <div class="flex flex-wrap gap-x-3 justify-between">
      <h3 class="text-gray-800 text-3xl font-thin text-gray-200">
        <router-link :to="{name: 'application.index'}" class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center mr-2">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </router-link>
        Create a new application
      </h3>
    </div>

    <div class="mt-4">
      <div class="p-6 bg-white rounded-md shadow-md mb-2">
        <ValidationAlert :errors="errors"></ValidationAlert>

        <form @submit.prevent="create" class="mt-2" id="create-application">
          <div class="mt-4">
            <label class="text-gray-700" for="name">Name</label>
            <input id="name" name="name" class="form-input w-full mt-2 rounded-md focus:border-primary-600" type="text"/>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="slug">Slug</label>
            <input id="slug" name="slug" class="form-input w-full mt-2 rounded-md focus:border-primary-600" type="text"/>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="description">Description</label>
            <textarea id="description" name="description" class="form-input w-full mt-2 rounded-md focus:border-primary-600"></textarea>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="icon">Icon</label>
            <input id="icon" name="icon" class="w-full mt-2" type="file"/>
          </div>

          <div class="flex mt-6">
            <button class="px-4 py-2 bg-primary-800 text-white rounded-md hover:bg-primary-600 focus:outline-none focus:bg-primary-700">
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </Base>
</template>

<script>
import Base from '../layout/Base';
import ValidationAlert from '../../components/ValidationAlert';

export default {
  name: 'ApplicationCreate',
  components: {Base, ValidationAlert},
  data() {
    return {
      errors: []
    }
  },
  methods: {
    create() {
      let form = new FormData(document.getElementById('create-application'));
      this.axios.post('/api/v1/applications', form).then(() => {
        this.$router.push({name: 'application.index'});
      }).catch(e => {
        this.errors = e.response.data.error.errors;
      });
    }
  }
}
</script>

<style scoped>

</style>
