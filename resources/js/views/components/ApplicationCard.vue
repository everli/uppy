<template>
  <div class="flex flex-col w-full md:w-1/2 lg:my-2 lg:px-2 lg:w-1/3 xl:my-2 xl:px-2 xl:w-1/3">
    <div class="flex-1 border border-gray-400 bg-white rounded p-4 flex flex-col justify-between leading-normal shadow-sm hover:shadow-lg mr-2 mt-2">
      <div class="justify-start">
        <div class="flex items-center">
          <img class="w-12 h-12 rounded-full mr-4" :src="'/applications/'+app.slug+'/icon'" alt="App Icon">
          <div class="text-base w-full">
            <div class="text-gray-900 font-bold flex justify-between flex-wrap">
              <div>{{ app.name }}</div>
              <a :href="'/'+ app.slug " target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 text-gray-600 fill-current align-middle inline-block">
                  <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                  <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z"/>
                </svg>
              </a>
            </div>
            <p class="text-gray-600 text-xs">{{ app.description }}</p>
          </div>
        </div>
        <hr class="mt-4 ">
        <div class="mt-2">
          <table class="text-sm mt-4 w-full text-center">
            <thead>
            <tr class="uppercase">
              <th>Platform</th>
              <th>Version</th>
              <th>Downloads</th>
              <th>Installs</th>
            </tr>
            </thead>
            <tbody v-for="build in app.builds" class="text-gray-800">
            <tr>
              <td>{{ build.platform }}</td>
              <td>{{ build.version }}</td>
              <td>{{ build.downloads.toLocaleString() }}</td>
              <td>{{ build.installations.toLocaleString() }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="justify-end">
        <div class="flex mt-6 justify-between">
          <router-link :to="{name: 'application.build.index', params: {id: app.id}}" class="px-2 py-1 border border-primary-800 text-primary-800 text-sm rounded hover:bg-primary-600 hover:text-white focus:outline-none focus:text-white focus:bg-primary-700 inline-flex items-center">
            <span>BUILDS</span>
          </router-link>
          <router-link :to="{name: 'application.build.upload', params: {id: app.id}}" class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center">
            <span>UPLOAD</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['app']
}
</script>
