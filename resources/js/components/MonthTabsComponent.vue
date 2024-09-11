<template>
  <div class="glass rounded-lg shadow-lg mb-6 overflow-hidden animate-fadeIn">
    <div class="month-tabs-container custom-scrollbar" ref="monthTabsContainer">
      <nav class="flex p-2" aria-label="Tabs">
        <a v-for="(month, index) in months"
           :key="month"
           :class="['month-tab', month === currentMonth ? 'active' : '']"
           @click="setCurrentMonth(month)"
           :ref="el => { if (month === currentMonth) currentMonthRef = el }"
           :style="{ animationDelay: `${index * 0.05}s` }">
          <i class="fas fa-calendar-day mr-2"></i>{{ month }}
        </a>
      </nav>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, nextTick } from 'vue';

export default {
  props: {
    months: {
      type: Array,
      required: true
    },
    currentMonth: {
      type: String,
      required: true
    }
  },
  emits: ['update:currentMonth'],
  setup(props, { emit }) {
    const monthTabsContainer = ref(null);
    const currentMonthRef = ref(null);

    const setCurrentMonth = (month) => {
      emit('update:currentMonth', month);
      nextTick(() => {
        if (currentMonthRef.value && monthTabsContainer.value) {
          const container = monthTabsContainer.value;
          const tab = currentMonthRef.value;
          const containerWidth = container.offsetWidth;
          const tabWidth = tab.offsetWidth;
          const scrollLeft = tab.offsetLeft - (containerWidth / 2) + (tabWidth / 2);
          container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
        }
      });
    };

    onMounted(() => {
      if (currentMonthRef.value && monthTabsContainer.value) {
        const container = monthTabsContainer.value;
        const tab = currentMonthRef.value;
        const containerWidth = container.offsetWidth;
        const tabWidth = tab.offsetWidth;
        const scrollLeft = tab.offsetLeft - (containerWidth / 2) + (tabWidth / 2);
        container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
      }
    });

    return {
      monthTabsContainer,
      currentMonthRef,
      setCurrentMonth
    };
  }
}
</script>

<style scoped>
.month-tabs-container {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.month-tabs-container::-webkit-scrollbar {
  display: none;
}

.month-tab {
  @apply px-4 py-2 text-sm font-medium rounded-md transition-all duration-300 cursor-pointer;
  animation: fadeInUp 0.5s ease-out forwards;
  opacity: 0;
  transform: translateY(10px);
}

.month-tab:not(.active) {
  @apply text-gray-500 hover:text-gray-700 hover:bg-gray-100;
}

.month-tab.active {
  @apply text-white bg-gradient-to-r from-blue-500 to-green-500 shadow-md;
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: var(--primary-color) #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: var(--primary-color);
  border-radius: 3px;
}
</style>
