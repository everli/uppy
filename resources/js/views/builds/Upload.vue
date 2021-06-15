<template>
  <Base>
    <div class="flex flex-wrap gap-x-3 justify-between">
      <h3 class="text-gray-800 text-3xl font-thin text-gray-200">
        <a @click="$router.go(-1)"
           class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center mr-2">
          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </a>
        {{ app.name }} - Upload
      </h3>
    </div>

    <div class="mt-4">
      <div
          class="border border-gray-400 bg-white rounded p-4 flex flex-col justify-between leading-normal shadow-sm hover:shadow-lg mr-2 mt-2">
        <ValidationAlert :errors="errors"></ValidationAlert>

        <form @submit.prevent="uploadBuild" class="mt-2" id="upload-build">
          <div class="mt-4">
            <label class="text-gray-700" for="version">Version</label>
            <input id="version" name="version" class="form-input w-full mt-2 rounded-md focus:border-primary-600"
                   type="text"/>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="available_from">Available from</label>
            <input id="available_from" name="available_from"
                   class="form-input w-full mt-2 rounded-md focus:border-primary-600" type="datetime-local"
                   placeholder="YYYY-MM-DD HH:mm:ss"/>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="forced">Forced update</label>
            <div class="mt-2">
              <label class="inline-flex items-center">
                <input type="hidden" value="off" name="forced"/>
                <input name="forced" id="forced" type="checkbox" class="form-checkbox text-primary-800 h-6 w-6"/>
                <span class="mx-2 text-gray-600 text-sm">This update is mandatory.</span>
              </label>
            </div>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="partial_rollout">Partial Rollout</label>
            <div class="mt-2">
              <label class="inline-flex items-center">
                <input type="hidden" value="off" name="partial_rollout"/>
                <input name="partial_rollout" id="partial_rollout" type="checkbox"
                       class="form-checkbox text-primary-800 h-6 w-6" v-model="partialRollout"/>
                <span class="mx-2 text-gray-600 text-sm">Enable partial rollout.</span>
              </label>
            </div>
            <div v-if="partialRollout" class="mt-2 inline-flex items-center w-full">
              <span class="mr-2 input-percentage">
                <input type="number" class="form-input" min="0" max="100" step="10" v-model="rolloutPercentage">
              </span>
              <div class="flex-grow">
                <input type="range" id="rollout_percentage" name="rollout_percentage" min="0" max="100" step="10"
                       class="rounded-lg overflow-hidden appearance-none bg-gray-400 h-4 w-full flex-grow"
                       v-model="rolloutPercentage">
              </div>
            </div>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="file">File</label>
            <input id="file" name="file" class="w-full mt-2" type="file"/>
          </div>

          <div class="mt-4">
            <label class="text-gray-700" for="file">Changelog</label>
            <ChangelogTable :changelogs="changelogs"></ChangelogTable>
          </div>

          <div class="flex mt-6" v-if="!uploading">
            <button type="submit" :disabled="uploading"
                    class="px-4 py-2 bg-primary-800 text-white rounded-md hover:bg-primary-600 focus:outline-none focus:bg-primary-700 disabled:opacity-75">
              Upload
            </button>
          </div>

          <div class="mt-6" v-if="uploading">
            <div class="text-left">
              <span class="text-xs font-semibold inline-block text-primary-800">
                {{ uploadPercentage }}%
              </span>
            </div>
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-primary-100">
              <div :style="{ width: uploadPercentage + '%' }"
                   class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary-800"></div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </Base>
</template>

<script>
import Base from '../layout/Base';
import ValidationAlert from '../components/ValidationAlert';
import ChangelogTable from "../components/ChangelogTable";
import {mapState} from "vuex";

export default {
  name: 'Upload',
  components: {ChangelogTable, ValidationAlert, Base},
  data() {
    return {
      errors: [],
      uploading: false,
      uploadPercentage: 0,
      partialRollout: false,
      rolloutPercentage: 50,
      changelogs: {}
    }
  },
  async created() {
    if (_.isEmpty(this.$store.state.application.applications)) {
      await this.$store.dispatch('application/getApplicationById', this.$route.params.application);
    }
    await this.$store.dispatch('application/setCurrentAppById', this.$route.params.application)
  },
  computed: {
    ...mapState({
      app: state => state.application.currentApp ?? {'name': null},
    })
  },
  methods: {
    uploadBuild() {
      this.uploading = true;
      this.errors = [];
      let form = new FormData(document.getElementById('upload-build'));

      for (const [key, value] of Object.entries(this.changelogs)) {
        form.append(`changelogs[${key}]`, value);
      }

      this.axios.post('/api/v1/applications/' + this.$route.params.application + '/builds', form, {
        headers: {'Content-Type': 'multipart/form-data'},

        onUploadProgress: function (progressEvent) {
          this.uploadPercentage = parseInt(Math.round((progressEvent.loaded / progressEvent.total) * 100));
        }.bind(this)

      }).then(() => this.$store.dispatch('build/getBuildsByApplication', this.$route.params.application))
          .then(() => {
            this.$router.push({
              name: 'application.build.index',
              params: {application: this.$route.params.application}
            });
          })
          .catch(e => {
            this.uploadPercentage = 0;
            this.uploading = false;
            this.errors = e.response.data.error.errors;
            if (this.errors.length === 0) {
              this.errors = [[e.response.data.error.message]];
            }
          });
    }
  }
}
</script>

<style scoped>

</style>
