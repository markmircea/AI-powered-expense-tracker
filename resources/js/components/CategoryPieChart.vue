<template>
  <div class="glass rounded-lg p-6 shadow-lg animate-fadeIn">
    <h2 class="text-2xl font-bold mb-6 gradient-text">Expense Categories for {{ currentMonthText }} {{ currentYear }}</h2>
    <div class="flex flex-col lg:flex-row">
      <div v-if="hasCategoryData" class="w-full lg:w-1/2 h-64 lg:h-auto mb-6 lg:mb-0">
        <Pie :data="chartData" :options="chartOptions" />
      </div>
      <div v-else class="w-full lg:w-1/2 h-64 lg:h-auto flex items-center justify-center mb-6 lg:mb-0">
        <p class="text-gray-500 text-lg">No expense data available for this month.</p>
      </div>
      <div class="w-full lg:w-1/2 lg:pl-6">
        <h3 class="text-xl font-semibold mb-4">Category Totals</h3>
        <ul v-if="hasCategoryData" class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar pr-2">
          <li v-for="(total, category) in sortedCategoryTotals" :key="category"
              class="flex justify-between items-center p-3 hover:bg-opacity-50 hover:bg-white rounded-md transition-all duration-300 animate-slideIn"
              :style="{ animationDelay: `${Object.keys(sortedCategoryTotals).indexOf(category) * 0.1}s` }">
            <span class="flex items-center">
              <span class="w-4 h-4 inline-block mr-3 rounded-full" :style="{ backgroundColor: getCategoryColor(category) }"></span>
              <i :class="['fas', getCategoryIcon(category), 'mr-2', 'text-gray-600']"></i>
              {{ category }}
            </span>
            <span class="font-semibold">{{ formatCurrency(total) }}</span>
          </li>
        </ul>
        <p v-else class="text-gray-500">No expense data available for this month.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref, watch } from 'vue';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

export default {
  name: 'CategoryPieChart',
  components: { Pie },
  props: {
    transactions: {
      type: Array,
      required: true
    },
    currentMonth: {
      type: Number,
      required: true
    },
    currentYear: {
      type: Number,
      required: true
    },
    currentMonthText: String
  },
  setup(props) {
    const chartInstance = ref(null);

    const pastelColors = [
      '#FFB3BA', '#FFDFBA', '#FFFFBA', '#BAFFC9', '#BAE1FF',
      '#FFC6FF', '#BFFCC6', '#D7FFFE', '#E7D8FF', '#FFF5BA',
      '#FF9AA2', '#C7CEEA', '#B5EAD7', '#FFB7B2', '#E2F0CB',
      '#FFDAC1', '#D4F0F0', '#F7D6E0', '#F2E3E1', '#C1EEFF'
    ];

    const getCategoryColor = (() => {
      const colorMap = {};
      let colorIndex = 0;

      return (category) => {
        if (!colorMap[category]) {
          colorMap[category] = pastelColors[colorIndex % pastelColors.length];
          colorIndex++;
        }
        return colorMap[category];
      };
    })();

    const getCategoryIcon = (category) => {
      const iconMap = {
        'Car': 'fa-car',
        'Cash Out': 'fa-money-bill-wave',
        'Communication': 'fa-comments',
        'Divertisment': 'fa-film',
        'Education': 'fa-graduation-cap',
        'Gifts': 'fa-gift',
        'Health': 'fa-heartbeat',
        'Insurance': 'fa-shield-alt',
        'Nicotine': 'fa-smoking',
        'Personal': 'fa-user',
        'Rent': 'fa-home',
        'Revolut': 'fa-credit-card',
        'Restaurant': 'fa-utensils',
        'Shopping': 'fa-shopping-cart',
        'Sport': 'fa-futbol',
        'Subscriptions': 'fa-newspaper',
        'Supermarket': 'fa-store',
        'Transport': 'fa-bus',
        'Travel': 'fa-plane',
        'Utilities': 'fa-bolt'
      };
      return iconMap[category] || 'fa-tag';
    };

    const filteredTransactions = computed(() => {
      return props.transactions.filter(transaction => {
        const transactionDate = new Date(transaction.date);
        return transactionDate.getMonth() === props.currentMonth &&
               transactionDate.getFullYear() === props.currentYear &&
               transaction.type === 'Expense';
      });
    });

    const categoryTotals = computed(() => {
      return filteredTransactions.value.reduce((acc, transaction) => {
        const amount = parseFloat(transaction.amount);
        if (!isNaN(amount) && amount > 0) {
          acc[transaction.category] = (acc[transaction.category] || 0) + amount;
        }
        return acc;
      }, {});
    });

    const sortedCategoryTotals = computed(() => {
      return Object.fromEntries(
        Object.entries(categoryTotals.value)
          .filter(([_, value]) => value > 0)
          .sort((a, b) => b[1] - a[1])
      );
    });

    const hasCategoryData = computed(() => {
      return Object.keys(sortedCategoryTotals.value).length > 0;
    });

    const chartData = computed(() => ({
      labels: Object.keys(sortedCategoryTotals.value),
      datasets: [{
        data: Object.values(sortedCategoryTotals.value),
        backgroundColor: Object.keys(sortedCategoryTotals.value).map(getCategoryColor),
        borderColor: 'rgba(255, 255, 255, 0.5)',
        borderWidth: 2,
      }]
    }));

    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
              return `${label}: ${formatCurrency(value)} (${percentage}%)`;
            }
          }
        }
      },
      animation: {
        animateScale: true,
        animateRotate: true
      }
    };

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('ro-RO', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value) + ' RON';
    };

    watch([() => props.currentMonth, () => props.currentYear], () => {
      if (chartInstance.value) {
        chartInstance.value.update();
      }
    });

    return {
      chartData,
      chartOptions,
      sortedCategoryTotals,
      getCategoryColor,
      getCategoryIcon,
      formatCurrency,
      chartInstance,
      hasCategoryData
    };
  }
};
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: var(--primary-color) #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: var(--primary-color);
  border-radius: 3px;
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
