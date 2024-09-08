<template>
    <div>
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="bankStatement" class="block text-sm font-medium text-gray-700">Bank Statement File</label>
          <input type="file" id="bankStatement" ref="bankStatement" @change="form.bankStatement = $event.target.files[0]" class="mt-1 block w-full" accept=".csv,.xlsx,.xls">
        </div>
        <button type="submit" class="btn bg-blue-500 hover:bg-blue-600 text-white" :disabled="form.processing">
          {{ form.processing ? 'Uploading...' : 'Upload and Process' }}
        </button>
      </form>
      <div v-if="form.recentlySuccessful" class="mt-4 text-green-600">
        Bank statement processed successfully.
      </div>
      <div v-if="form.errors.bankStatement" class="mt-4 text-red-600">
        {{ form.errors.bankStatement }}
      </div>
    </div>
  </template>

  <script>
  import { useForm } from '@inertiajs/vue3';

  export default {
    emits: ['transactions-updated'],
    setup(props, { emit }) {
      const form = useForm({
        bankStatement: null,
      });

      const submit = () => {
        form.post('/api/upload-bank-statement', {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            emit('transactions-updated', page.props.transactions);
          },
        });
      };

      return { form, submit };
    },
  };
  </script>
