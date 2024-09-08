<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
      <HeaderComponent :currentMonth="currentMonth" :currentYear="currentYear" />

      <main class="flex-grow p-4 sm:p-6 lg:p-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
          <h1 class="text-2xl font-semibold mb-6">Upload Bank Statement</h1>
          <p>Debug Info: {{ debugInfo }}</p>
          <BankStatementUploadComponent @transactions-updated="handleUploadedTransactions" />
        </div>
      </main>
    </div>
  </template>

  <script>
  import { ref } from 'vue';
  import HeaderComponent from '../components/HeaderComponent.vue';
  import BankStatementUploadComponent from '../components/BankStatementUploadComponent.vue';

  export default {
    components: {
      HeaderComponent,
      BankStatementUploadComponent,
    },
    props: {
      debugInfo: String,
    },
    setup(props) {
      const currentMonth = ref('September');
      const currentYear = ref(2024);
      const uploadedTransactions = ref([]);

      const handleUploadedTransactions = (transactions) => {
        uploadedTransactions.value = transactions;
        // You can add additional logic here, such as showing a success message
        // or redirecting to the main spreadsheet view
      };

      return {
        currentMonth,
        currentYear,
        uploadedTransactions,
        handleUploadedTransactions,
        debugInfo: props.debugInfo,
      };
    },
  };
  </script>
