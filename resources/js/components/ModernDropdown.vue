<template>
    <div class="relative w-full">
      <button
        @click="toggleDropdown"
        class="w-full px-4 py-2 text-left bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-300 hover:bg-gray-50"
      >
        <span class="block truncate">{{ selectedOption ? selectedOption.label : 'Select an option' }}</span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </span>
      </button>

      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div v-if="isOpen" class="absolute z-10 w-full mt-1 bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
          <div
            v-for="option in options"
            :key="option.value"
            @click="selectOption(option)"
            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 transition-colors duration-200"
            :class="{ 'bg-indigo-100 text-indigo-900': selectedOption && selectedOption.value === option.value, 'text-gray-900': !(selectedOption && selectedOption.value === option.value) }"
          >
            <span :class="{ 'font-semibold': selectedOption && selectedOption.value === option.value, 'font-normal': !(selectedOption && selectedOption.value === option.value) }" class="block truncate">
              {{ option.label }}
            </span>
          </div>
        </div>
      </transition>
    </div>
  </template>

  <script>
  import { ref, watch } from 'vue';

  export default {
    name: 'ModernDropdown',
    props: {
      options: {
        type: Array,
        required: true
      },
      modelValue: {
        type: [String, Number, Object],
        default: null
      }
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
      const isOpen = ref(false);
      const selectedOption = ref(props.modelValue);

      watch(() => props.modelValue, (newValue) => {
        selectedOption.value = newValue;
      });

      const toggleDropdown = () => {
        isOpen.value = !isOpen.value;
      };

      const selectOption = (option) => {
        selectedOption.value = option;
        emit('update:modelValue', option);
        isOpen.value = false;
      };

      return {
        isOpen,
        selectedOption,
        toggleDropdown,
        selectOption
      };
    }
  };
  </script>
