<template>
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6 mt-6">
        <div class="summary-item">
            <div class="summary-content">
                <dt class="summary-label">
                    <i class="fas fa-arrow-up text-green-600 mr-2"></i>Total Income
                </dt>
                <dd class="summary-value income">{{ formatCurrency(totalIncome) }}</dd>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-content">
                <dt class="summary-label">
                    <i class="fas fa-arrow-down text-red-600 mr-2"></i>Total Expenses
                </dt>
                <dd class="summary-value expense">{{ formatCurrency(totalExpenses) }}</dd>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-content">
                <dt class="summary-label">
                    <i class="fas fa-balance-scale mr-2"></i>Net
                </dt>
                <dd class="summary-value net">{{ formatCurrency(netAmount) }}</dd>
            </div>
        </div>
        <TrophyCardComponent :totalNet="totalNetAllMonths" />
    </div>
</template>

<script>
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
    methods: {
        formatCurrency(value) {
            return new Intl.NumberFormat('ro-RO', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value) + ' RON';
        }
    }
}
</script>
