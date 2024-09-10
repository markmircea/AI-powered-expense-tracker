<template>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-6">Create New Team</h1>

      <form @submit.prevent="submitForm" class="max-w-md">
        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700">Team Name</label>
          <input
            type="text"
            id="name"
            v-model="form.name"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            required
          >
        </div>

        <div v-if="form.errors.name" class="text-red-600 text-sm mb-4">{{ form.errors.name }}</div>

        <button
          type="submit"
          class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition duration-300"
          :disabled="form.processing"
        >
          Create Team
        </button>
      </form>
    </div>
  </template>

  <script>
  import { useForm } from '@inertiajs/vue3';

  export default {
    setup() {
      const form = useForm({
        name: '',
      });

      const submitForm = () => {
        form.post('/teams', {
          preserveScroll: true,
          onSuccess: () => form.reset('name'),
        });
      };

      return {
        form,
        submitForm,
      };
    },
  };
  </script>
