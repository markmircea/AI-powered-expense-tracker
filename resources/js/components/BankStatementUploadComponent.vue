<template>
  <div class="bank-statement-upload">
    <form @submit.prevent="submit" class="space-y-4">
      <div
        class="file-upload-container"
        @dragover.prevent="dragover"
        @dragleave="dragleave"
        @drop.prevent="drop"
      >
        <label for="bankStatement" class="file-upload-label">
          <span class="file-upload-icon">
            <i class="fas fa-cloud-upload-alt"></i>
          </span>
          <span class="file-upload-text">
            {{ form.bankStatement ? form.bankStatement.name : 'Drag and drop or click to upload a bank statement file' }}
          </span>
        </label>
        <input
          type="file"
          id="bankStatement"
          ref="bankStatement"
          @change="handleFileChange"
          class="hidden"
          accept=".csv,.xlsx,.xls"
        >
      </div>
      <button
        type="submit"
        class="btn bg-blue-500 hover:bg-blue-600 text-white w-full transition-all duration-300 ease-in-out transform hover:scale-105"
        :disabled="form.processing || !form.bankStatement"
        :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.bankStatement }"
      >
        <span class="flex items-center justify-center">
          <i class="fas fa-upload mr-2"></i>
          {{ form.processing ? 'Uploading...' : 'Upload and Process' }}
        </span>
      </button>
    </form>
    <transition name="fade">
      <div v-if="form.recentlySuccessful" class="mt-4 text-green-600 success-message">
        <i class="fas fa-check-circle mr-2"></i>
        Bank statement processed successfully.
      </div>
    </transition>
    <transition name="fade">
      <div v-if="form.errors.bankStatement" class="mt-4 text-red-600 error-message">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ form.errors.bankStatement }}
      </div>
    </transition>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';
import { ref, inject } from 'vue';

export default {
    emits: ['transactions-updated'],
  setup(props, { emit }) {
    const selectedTeamId = inject('selectedTeamId'); // Inject the selected team ID

    const form = useForm({
      bankStatement: null,
      teamId: null, // Add this line
    });
    const fileInputRef = ref(null);

    const handleFileChange = (event) => {
      const file = event.target.files[0];
      if (file) {
        form.bankStatement = file;
      }
    };

    const dragover = (event) => {
      event.target.classList.add('border-blue-500');
    };

    const dragleave = (event) => {
      event.target.classList.remove('border-blue-500');
    };

    const drop = (event) => {
      event.target.classList.remove('border-blue-500');
      const file = event.dataTransfer.files[0];
      if (file) {
        form.bankStatement = file;
      }
    };

    const submit = () => {
      form.teamId = selectedTeamId.value; // Set the team ID before submitting
      form.post('/api/upload-bank-statement', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
          emit('transactions-updated', page.props.transactions);
        },
      });
    };

    return { form, submit, fileInputRef, handleFileChange, dragover, dragleave, drop };
  },
};
</script>

<style scoped>
.bank-statement-upload {
  @apply p-6 bg-white rounded-lg shadow-md;
}

.file-upload-container {
  @apply relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition-all duration-300 ease-in-out hover:border-blue-500;
}

.file-upload-label {
  @apply flex flex-col items-center justify-center;
}

.file-upload-icon {
  @apply text-4xl text-gray-400 mb-2;
}

.file-upload-text {
  @apply text-sm text-gray-600;
}

.success-message,
.error-message {
  @apply p-3 rounded-md text-sm;
}

.success-message {
  @apply bg-green-100;
}

.error-message {
  @apply bg-red-100;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
