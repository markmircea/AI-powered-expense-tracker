<template>
  <div class="glass rounded-lg shadow-lg p-6 mb-6 animate-fadeIn">
    <h2 @click="toggleNewEntryForm" class="text-xl font-bold mb-4 flex items-center cursor-pointer gradient-text">
      <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Add New Entry
      <i :class="['fas', 'ml-2', 'transition-transform', 'duration-300', isNewEntryFormExpanded ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
    </h2>
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-300 ease-in"
      @enter="onEnter"
      @leave="onLeave"
    >
      <form v-show="isNewEntryFormExpanded || !isMobile" @submit.prevent="addNewEntry" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
        <div class="flex items-center space-x-4">
          <button type="button" @click="newEntry.user = 'Mark'"
            :class="['user-icon', newEntry.user === 'Mark' ? 'mark' : 'bg-gray-300', 'transition-all duration-300']">
            M
          </button>
          <button type="button" @click="newEntry.user = 'Roxi'"
            :class="['user-icon', newEntry.user === 'Roxi' ? 'roxi' : 'bg-gray-300', 'transition-all duration-300']">
            R
          </button>
        </div>
        <div class="animate-slideIn" style="animation-delay: 0.1s;">
          <input v-model="newEntry.description" placeholder="Description" class="form-input rounded-lg shadow-sm w-full transition-all duration-300 focus:ring-2 focus:ring-indigo-500" required>
        </div>
        <div class="animate-slideIn" style="animation-delay: 0.2s;">
          <input v-model.number="newEntry.amount" type="number" placeholder="Amount" class="form-input rounded-lg shadow-sm w-full transition-all duration-300 focus:ring-2 focus:ring-indigo-500" required>
        </div>
        <div class="animate-slideIn" style="animation-delay: 0.3s;">
          <select v-model="newEntry.type" class="form-select rounded-lg shadow-sm w-full transition-all duration-300 focus:ring-2 focus:ring-indigo-500" required>
            <option value="">Select Type</option>
            <option value="Income">Income</option>
            <option value="Expense">Expense</option>
          </select>
        </div>
        <div class="relative animate-slideIn" style="animation-delay: 0.4s;">
          <select v-model="newEntry.category" class="form-select rounded-lg shadow-sm w-full transition-all duration-300 focus:ring-2 focus:ring-indigo-500" required>
            <option value="">Select or type Category</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
          <input v-model="newEntry.category" placeholder="Custom Category" class="form-input rounded-lg shadow-sm mt-2 w-full transition-all duration-300 focus:ring-2 focus:ring-indigo-500" v-if="!categories.includes(newEntry.category)">
        </div>
        <div class="animate-slideIn" style="animation-delay: 0.5s;">
          <input v-model="newEntry.date" type="date" class="form-input rounded-lg shadow-sm w-full transition-all duration-300 focus:ring-2 focus:ring-indigo-500" required>
        </div>
        <div class="animate-slideIn" style="animation-delay: 0.6s;">
          <button type="submit" class="btn btn-primary w-full">
            <i class="fas fa-save mr-2"></i>Add Entry
          </button>
        </div>
      </form>
    </transition>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';

export default {
  props: {
    categories: {
      type: Array,
      required: true
    },
    currentMonth: {
      type: String,
      required: true
    },
    currentYear: {
      type: Number,
      required: true
    },
    months: {
      type: Array,
      required: true
    },
    currentUserId: {
      type: Number,
      required: true
    },
    selectedTeamId: {
      type: Number,
      required: false,
      default: null
    },
    auth: Object
  },
  emits: ['add-new-entry'],
  setup(props, { emit }) {
    const isNewEntryFormExpanded = ref(false);
    const isMobile = ref(false);

    const newEntry = ref({
      user: '',
      description: '',
      amount: null,
      type: '',
      category: '',
      date: computed(() => new Date(props.currentYear, props.months.indexOf(props.currentMonth), 1).toISOString().split('T')[0]),
      user_id: props.currentUserId,
      team_id: props.selectedTeamId
    });

    const toggleNewEntryForm = () => {
      if (isMobile.value) {
        isNewEntryFormExpanded.value = !isNewEntryFormExpanded.value;
      }
    };

    const onEnter = (el) => {
      el.style.maxHeight = el.scrollHeight + 'px';
    };

    const onLeave = (el) => {
      el.style.maxHeight = '0px';
    };

    const addNewEntry = () => {
      emit('add-new-entry', { ...newEntry.value });
      newEntry.value = {
        user: '',
        description: '',
        amount: null,
        type: '',
        category: '',
        date: new Date(props.currentYear, props.months.indexOf(props.currentMonth), 1).toISOString().split('T')[0],
        user_id: props.auth.user.id,
        team_id: props.selectedTeamId
      };
    };

    onMounted(() => {
      const checkMobile = () => {
        isMobile.value = window.innerWidth < 640;
      };
      checkMobile();
      window.addEventListener('resize', checkMobile);
    });

    return {
      newEntry,
      isNewEntryFormExpanded,
      isMobile,
      toggleNewEntryForm,
      onEnter,
      onLeave,
      addNewEntry
    };
  }
}
</script>

<style scoped>
.user-icon {
  @apply w-10 h-10 rounded-full text-white font-bold flex items-center justify-center transition-all duration-300;
}

.user-icon.mark {
  @apply bg-blue-500;
}

.user-icon.roxi {
  @apply bg-pink-500;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slideIn {
  animation: slideIn 0.5s ease-out forwards;
}
</style>
