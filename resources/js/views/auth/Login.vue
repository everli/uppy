<template>
  <div class="flex justify-center items-center h-screen bg-gray-200 px-6">
    <div class="p-6 max-w-sm w-full bg-white shadow-md rounded-md">
      <div class="flex justify-center items-center mb-2">
        <span class="text-gray-700 font-thin text-3xl">{{ app_name }}</span>
      </div>

      <ValidationAlert :errors="errors"></ValidationAlert>

      <form class="mt-2" @submit.prevent="login">
        <label class="block">
          <span class="text-gray-700 text-sm">Email</span>
          <input type="email" class="form-input mt-1 block w-full rounded-md focus:border-primary-hover" v-model="form.email"/>
        </label>

        <label class="block mt-3">
          <span class="text-gray-700 text-sm">Password</span>
          <input type="password" class="form-input mt-1 block w-full rounded-md focus:border-primary-hover" v-model="form.password"/>
        </label>

        <div class="flex justify-between items-center mt-4">
          <div>
            <label class="inline-flex items-center">
              <input type="checkbox" class="form-checkbox text-uppy-primary" v-model="form.remember"/>
              <span class="mx-2 text-gray-600 text-sm">Remember me</span>
            </label>
          </div>
        </div>

        <div class="mt-6">
          <button type="submit" class="py-2 px-4 text-center bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 w-full">Login</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import ValidationAlert from '../../components/ValidationAlert';

export default {
  name: 'Login',
  components: {ValidationAlert},
  data() {
    return {
      app_name: this.$store.state.config.app_name,
      form: {
        email: '',
        password: '',
        remember: false,
      },
      errors: null,
    }
  },
  methods: {
    login() {
      this.axios.get('/sanctum/csrf-cookie').then(() => {
        this.axios.post('/login', this.form).then(() => {
          this.$store.dispatch('setAuthState', true);
          this.$router.push({name: 'application.index'});
        }).catch(e => {
          this.errors = e.response.data.errors;
        });
      });
    }
  }
}
</script>

<style scoped>

</style>
