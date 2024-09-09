<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <HeaderComponent :currentMonth="currentMonth" :currentYear="currentYear" />

    <main class="flex-grow p-4 sm:p-6 lg:p-8">
      <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-semibold mb-6">Upload Bank Statement</h1>
        <BankStatementUploadComponent @transactions-updated="handleUploadedTransactions" />

        <div class="mt-8">
          <h2 class="text-xl font-semibold mb-4">Previous Uploads</h2>
          <div v-if="previousUploads.length === 0" class="text-gray-500">
            No previous uploads found.
          </div>
          <ul v-else class="space-y-4">
            <li v-for="upload in previousUploads" :key="upload.id" class="bg-gray-50 p-4 rounded-lg flex items-center justify-between">
              <div>
                <span class="font-medium">{{ upload.name }}</span>
                <span class="text-sm text-gray-500 ml-2">({{ formatFileSize(upload.size) }})</span>
              </div>
              <div class="flex space-x-2">
                <button @click="downloadFile(upload)" class="text-blue-600 hover:text-blue-800">
                  <i class="fas fa-download"></i>
                </button>
                <button @click="deleteFile(upload)" class="text-red-600 hover:text-red-800">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HeaderComponent from '../components/HeaderComponent.vue';
import BankStatementUploadComponent from '../components/BankStatementUploadComponent.vue';

export default {
  components: {
    HeaderComponent,
    BankStatementUploadComponent,
  },
  setup() {
    const currentMonth = ref('September');
    const currentYear = ref(2024);
    const previousUploads = ref([]);

    const form = useForm({});

    const handleUploadedTransactions = (transactions) => {
      // Refresh the list of previous uploads
      fetchPreviousUploads();
    };

    const fetchPreviousUploads = async () => {
      try {
        const response = await axios.get('/api/previous-uploads');
        previousUploads.value = response.data;
      } catch (error) {
        console.error('Error fetching previous uploads:', error);
      }
    };

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const downloadFile = (upload) => {
      window.open(`/api/download-upload/${upload.id}`, '_blank');
    };

    const deleteFile = async (upload) => {
      if (confirm('Are you sure you want to delete this file?')) {
        try {
          await axios.delete(`/api/delete-upload/${upload.id}`);
          fetchPreviousUploads();
        } catch (error) {
          console.error('Error deleting file:', error);
        }
      }
    };

    onMounted(() => {
      fetchPreviousUploads();
    });

    return {
      currentMonth,
      currentYear,
      previousUploads,
      handleUploadedTransactions,
      formatFileSize,
      downloadFile,
      deleteFile,
    };
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
