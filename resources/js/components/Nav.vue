<template>
  <nav class="flex items-center justify-between flex-wrap p-6">
    <router-link :to="{name: 'application.index'}" class="flex items-center flex-shrink-0 mr-6 text-primary-800">
      <svg class="fill-current h-6 w-6 mr-1 transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="--transform-rotate: -20deg">
        <path fill-rule="evenodd" d="M9.243 3.03a1 1 0 01.727 1.213L9.53 6h2.94l.56-2.243a1 1 0 111.94.486L14.53 6H17a1 1 0 110 2h-2.97l-1 4H15a1 1 0 110 2h-2.47l-.56 2.242a1 1 0 11-1.94-.485L10.47 14H7.53l-.56 2.242a1 1 0 11-1.94-.485L5.47 14H3a1 1 0 110-2h2.97l1-4H5a1 1 0 110-2h2.47l.56-2.243a1 1 0 011.213-.727zM9.03 8l-1 4h2.938l1-4H9.031z" clip-rule="evenodd" />
      </svg>
      <span class="font-semibold text-xl tracking-tight">{{ $store.state.config.app_name }}</span>
    </router-link>
    <div class="relative">
        <button @click="dropdownOpen=!dropdownOpen" class="relative z-10 block h-8 w-8 rounded-full overflow-hidden focus:outline-none text-primary-800">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </button>

        <div v-show="dropdownOpen" @click="dropdownOpen=false" class="fixed inset-0 h-full w-full z-10"></div>
        <div v-show="dropdownOpen" class="absolute right-0 mt-2 py-2 w-48 bg-white rounded-md shadow-xl z-20">
          <a href="#" @click="logout()" class="flex px-4 py-2 text-sm text-primary-800 hover:bg-primary-800 hover:text-white">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
            </svg>
            Logout
          </a>
        </div>
      </div>
  </nav>
</template>


<script>
export default {
  name: 'Nav',
  data() {
    return {
      dropdownOpen: false
    }
  },
  methods: {
    logout() {
      this.axios.post('/logout', this.form).then(() => {
        this.$store.dispatch('setAuthState', false);
        this.$router.go(0);
      }).catch(error => console.log(error));
    }
  }
}
</script>

<style scoped>

</style>
