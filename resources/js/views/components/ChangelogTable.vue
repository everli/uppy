<template>
  <div>
    <table class="w-full mt-2">
      <thead>
      <tr class="uppercase">
        <th class="text-left">Locale</th>
        <th>Content</th>
        <th class="text-right">
          <button type="button"
                  class="px-2 py-1 bg-primary-800 text-white rounded hover:bg-primary-600 focus:outline-none focus:bg-primary-700 inline-flex items-center uppercase"
                  @click="openModal()">
            <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
          </button>
        </th>
      </tr>
      </thead>
      <tbody v-for="(content, locale) in changelogs" class="text-gray-800">
      <tr class="border-t">
        <td class="w-1/6 text-left pt-3">{{ locale }}</td>
        <td class="w-auto prose max-w-full" v-html="markdown(content)"></td>
        <td class="w-1/6 text-right">
          <button type="button"
                  class="px-2 py-1 bg-orange-600 text-white rounded hover:bg-orange-400 focus:outline-none focus:bg-orange-500 inline-flex items-center uppercase"
                  @click="editChangelog(locale)">
            <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
          </button>
          <button type="button"
                  class="px-2 py-1 bg-red-800 text-white rounded hover:bg-red-600 focus:outline-none focus:bg-red-700 inline-flex items-center uppercase"
                  @click="deleteChangelog(locale)">
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
    <div
        class="main-modal fixed w-full h-100 inset-0 z-50 overflow-hidden flex justify-center items-center animated"
        style="background: rgba(0,0,0,.7);"
        :class="{fadeIn: modalOpen, fadeOut: !modalOpen}">
      <div class="shadow-lg modal-container bg-white w-11/12 md:max-w-5xl mx-auto rounded shadow-lg z-50 overflow-y-auto changelog-modal">
        <div class="modal-content py-4 text-left px-6">
          <div class="flex justify-between items-center pb-3">
            <p class="text-2xl font-bold">Add changelog</p>
            <div class="modal-close cursor-pointer z-50" @click="modalOpen=false">
              <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                   viewBox="0 0 18 18">
                <path
                    d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                </path>
              </svg>
            </div>
          </div>
          <div class="my-5">
            <label class="text-gray-700 text-sm" for="locale">Locale</label>
            <select id="locale" class="form-input w-full rounded-md focus:border-primary-600" v-model="currentLocale">
              <option v-for="lang in this.languages" :value=lang.code>{{ lang.language }}</option>
            </select>
            <label class="text-gray-700 text-sm mt-4" for="content">Content</label>
            <textarea id="content" class="form-input w-full rounded-md focus:border-primary-600" v-model="currentContent"></textarea>
            <label class="text-gray-700 text-sm mt-4" for="preview">Preview</label>
            <div class="border border-gray-400 rounded p-2 prose max-w-full" id="preview" v-html="markdown(currentContent)"></div>
          </div>
          <div class="flex justify-end pt-2">
            <button type="button"
                    class="focus:outline-none modal-close px-4 bg-gray-400 p-3 rounded-lg text-black hover:bg-gray-300"
                    @click="cancelModal()">
              Cancel
            </button>
            <button type="button"
                    class="focus:outline-none px-4 bg-primary-800 p-3 ml-3 rounded-lg text-white hover:bg-primary-600"
                    @click="saveChangelog()">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import languages from '../../config/languages.json'

export default {
  name: "ChangelogTable",
  props: ['changelogs'],
  data() {
    return {
      currentLocale: 'en',
      currentContent: '',
      modalOpen: false,
      languages: languages.list
    }
  },
  methods: {
    openModal() {
      this.currentContent = '';
      this.modalOpen = true;
    },
    saveChangelog() {
      this.changelogs[this.currentLocale] = this.currentContent;
      this.cancelModal();
    },
    cancelModal() {
      this.currentLocale = 'en';
      this.currentContent = '';
      this.modalOpen = false;
    },
    editChangelog(locale) {
      this.currentLocale = locale;
      this.modalOpen = true;
      this.currentContent = this.changelogs[locale];
    },
    deleteChangelog(locale) {
      this.$delete(this.changelogs, locale);
    },
    markdown(value) {
      return marked.parse(value);
    },
  }
}
</script>

<style scoped>

.changelog-modal {
  max-height: 90%;
}

.animated {
  -webkit-animation-duration: 200ms;
  animation-duration: 200ms;
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
}

.fadeIn {
  -webkit-animation-name: fadeIn;
  animation-name: fadeIn;
}

.fadeOut {
  -webkit-animation-name: fadeOut;
  animation-name: fadeOut;
  @apply hidden
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }

  to {
    opacity: 0;
  }
}
</style>
