<template>
  <div
      class="flex-1 border border-gray-400 bg-white rounded p-4 flex flex-col justify-between leading-normal shadow-sm hover:shadow-lg mr-2 mt-2">
    <div class="justify-start">
      <div class="flex items-center">
        <div class="w-full text-3xl text-center">
          <h3 class="text-gray-900 font-thin">
            {{ platform }}
          </h3>
        </div>
      </div>
      <div class="mt-2">
        <table class="mt-4 w-full text-center">
          <thead>
          <tr class="uppercase">
            <th>Version</th>
            <th>Available From</th>
            <th>Installs</th>
            <th>Rollout</th>
            <th>Installs</th>
            <th>Dismissed</th>
            <th></th>
          </tr>
          </thead>
          <tbody v-for="(build, index) in builds" class="text-gray-800">
          <tr class="border-t">
            <td>{{ build.version }}</td>
            <td>{{ build.available_from }}</td>
            <td>{{ build.installations.toLocaleString() }}</td>
            <td>{{ build.rollout_percentage.toLocaleString() }} %</td>
            <td>{{ build.installations_percent.toLocaleString() }} %</td>
            <td>
              <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                <input type="checkbox" @change="updateDismissStatus($event, build)" :checked="build.dismissed"
                       class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                <label :for="'dismissed-'+build.id"
                       class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
              </div>
            </td>
            <td class="py-2">
              <router-link :to="{name: 'application.build.edit', params: {application: build.application_id, build: build.id}}"
                      class="px-2 py-1 bg-orange-600 text-white rounded hover:bg-orange-400 focus:outline-none focus:bg-orange-500 inline-flex items-center uppercase">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
              </router-link>
              <button type="button" @click="deleteBuild(build, index)"
                      class="px-2 py-1 bg-red-800 text-white rounded hover:bg-red-600 focus:outline-none focus:bg-red-700 inline-flex items-center uppercase">
                <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlatformCard',
  props: ['builds', 'platform'],
  methods: {
    updateDismissStatus(e, build) {
      if (!confirm('Are you sure you want update the status?')) {
        e.target.checked = !e.target.checked;
        return;
      }
      this.axios.post('/api/v1/builds/' + build.id, {
        dismissed: e.target.checked
      })
    },
    deleteBuild(build, index) {
      if (!confirm('Are you sure you want delete this build?')) {
        return;
      }
      this.axios.delete('/api/v1/builds/' + build.id)
          .then(() => this.builds.splice(index, 1));
    }
  }
}
</script>

<style scoped lang="scss">
.toggle-checkbox:checked {
  @apply right-0 border-primary-400;
}

.toggle-checkbox:checked + .toggle-label {
  @apply bg-primary-400;
}
</style>
