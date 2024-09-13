<template>
  <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md">
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div v-if="$page.props.flash.success && show" class="flex items-center justify-between mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md">
        <div class="flex items-center">
          <svg class="shrink-0 mr-2 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div class="text-sm font-medium">{{ $page.props.flash.success }}</div>
        </div>
        <button type="button" class="text-green-700 hover:text-green-900" @click="hideMessage">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </transition>

    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div v-if="($page.props.flash.error || Object.keys($page.props.errors).length > 0) && show" class="flex items-center justify-between mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md">
        <div class="flex items-center">
          <svg class="shrink-0 mr-2 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div v-if="$page.props.flash.error" class="text-sm font-medium">{{ $page.props.flash.error }}</div>
          <div v-else class="text-sm font-medium">
            <span v-if="Object.keys($page.props.errors).length === 1">There is one form error.</span>
            <span v-else>There are {{ Object.keys($page.props.errors).length }} form errors.</span>
          </div>
        </div>
        <button type="button" class="text-red-700 hover:text-red-900" @click="hideMessage">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watchEffect, onMounted, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
let timeout = null;

const showMessage = () => {
  show.value = true;
  setAutoHideTimer();
};

const hideMessage = () => {
  show.value = false;
  clearAutoHideTimer();
};

const setAutoHideTimer = () => {
  clearAutoHideTimer();
  timeout = setTimeout(() => {
    hideMessage();
  }, 10000); // Hide message after 10 seconds
};

const clearAutoHideTimer = () => {
  if (timeout) {
    clearTimeout(timeout);
  }
};

watchEffect(() => {
  if (page.props.flash.success || page.props.flash.error || Object.keys(page.props.errors).length > 0) {
    showMessage();
  }
});

onMounted(() => {
  if (page.props.flash.success || page.props.flash.error || Object.keys(page.props.errors).length > 0) {
    showMessage();
  }
});

onBeforeUnmount(() => {
  clearAutoHideTimer();
});
</script>
