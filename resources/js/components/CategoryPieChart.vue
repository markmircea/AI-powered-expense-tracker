<template>
    <div class="flex flex-col md:flex-row">
      <div v-if="hasCategoryData" class="w-full md:w-1/2 h-64 md:h-auto">
        <Pie :data="chartData" :options="chartOptions" />
      </div>
      <div v-else class="w-full md:w-1/2 h-64 md:h-auto flex items-center justify-center">
        <p class="text-gray-500 text-lg">No expense data available for this month.</p>
      </div>
      <div class="w-full md:w-1/2 mt-4 md:mt-0 md:ml-4">
        <h3 class="text-lg font-semibold mb-2">Category Totals</h3>
        <ul v-if="hasCategoryData" class="space-y-2 max-h-64 overflow-y-auto custom-scrollbar">
          <li v-for="(total, category) in sortedCategoryTotals" :key="category" class="flex justify-between items-center p-2 hover:bg-gray-100 rounded-md transition-colors duration-200">
            <span class="flex items-center">
              <span class="w-4 h-4 inline-block mr-2 rounded-full" :style="{ backgroundColor: getCategoryColor(category) }"></span>
              <i :class="['fas', getCategoryIcon(category), 'mr-2', 'text-gray-600']"></i>
              {{ category }}
            </span>
            <span class="font-semibold">{{ formatCurrency(total) }}</span>
          </li>
        </ul>
        <p v-else class="text-gray-500">No expense data available for this month.</p>
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
      }
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
