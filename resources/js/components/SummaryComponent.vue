<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-8 animate-fadeIn">
        <div class="summary-item glass p-6 rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
            <div class="summary-content">
                <dt class="summary-label text-lg font-semibold mb-2 gradient-text">
                    <i class="fas fa-arrow-up text-green-600 mr-2"></i>Total Income
                </dt>
                <dd class="summary-value income text-2xl font-bold">{{ formatCurrency(totalIncome) }}</dd>
            </div>
        </div>
        <div class="summary-item glass p-6 rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
            <div class="summary-content">
                <dt class="summary-label text-lg font-semibold mb-2 gradient-text">
                    <i class="fas fa-arrow-down text-red-600 mr-2"></i>Total Expenses
                </dt>
                <dd class="summary-value expense text-2xl font-bold">{{ formatCurrency(totalExpenses) }}</dd>
            </div>
        </div>
        <div class="summary-item glass p-6 rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105">
            <div class="summary-content">
                <dt class="summary-label text-lg font-semibold mb-2 gradient-text">
                    <i class="fas fa-balance-scale mr-2"></i>Net
                </dt>
                <dd class="summary-value net text-2xl font-bold" :class="netAmount >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(netAmount) }}
                </dd>
            </div>
        </div>
        <TrophyCardComponent :totalNet="totalNetAllMonths" class="glass rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-105" />
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import TrophyCardComponent from './TrophyCardComponent.vue';

export default {
    components: {
        TrophyCardComponent
    },
    props: {
        totalIncome: {
            type: Number,
            required: true
        },
        totalExpenses: {
            type: Number,
            required: true
        },
        netAmount: {
            type: Number,
            required: true
        },
        totalNetAllMonths: {
            type: Number,
            required: true
        }
    },
    setup(props) {
        const animate = ref(false);

        onMounted(() => {
            setTimeout(() => {
                animate.value = true;
            }, 100);
        });

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('ro-RO', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value) + ' RON';
        };

        return {
            animate,
            formatCurrency
        };
    }
}
</script>

<style scoped>
.summary-item {
    @apply relative overflow-hidden;
}

.summary-item::before {
    content: '';
    @apply absolute inset-0 bg-gradient-to-r from-blue-500 to-green-500 opacity-0 transition-opacity duration-300;
}

.summary-item:hover::before {
    @apply opacity-10;
}

.summary-content {
    @apply relative z-10;
}

.summary-value {
    @apply transition-all duration-300;
}

.summary-item:hover .summary-value {
    @apply scale-110 transform;
}

@keyframes count-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-count-up {
    animation: count-up 0.5s ease-out forwards;
}
</style>
