<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <HeaderComponent :currentMonth="currentMonth" :currentYear="currentYear" />

        <main class="flex-grow p-4 sm:p-6 lg:p-8">
            <MonthTabsComponent :months="months" :currentMonth="currentMonth" @update:currentMonth="setCurrentMonth" />

            <SummaryComponent
                :totalIncome="totalIncome"
                :totalExpenses="totalExpenses"
                :netAmount="netAmount"
                :totalNetAllMonths="totalNetAllMonths"
            />



            <!-- Category Pie Chart -->
            <div class="bg-white rounded-lg shadow mt-6 mb-6 p-4">
                <h2 class="text-lg font-semibold mb-4">Expense Categories for {{ currentMonth }} {{ currentYear }}</h2>
                <CategoryPieChart :transactions="filteredRowData" :currentMonth="months.indexOf(currentMonth)"
                    :currentYear="currentYear" />
            </div>

             <!-- Bank Statement Upload Component -->
             <div class="bg-white rounded-lg shadow mt-6 mb-6 p-4">
                <BankStatementUploadComponent @transactions-updated="handleUploadedTransactions" />
            </div>

            <NewEntryFormComponent
                :categories="categories"
                :currentMonth="currentMonth"
                :currentYear="currentYear"
                :months="months"
                @add-new-entry="addNewEntry"
            />

            <SpreadsheetGridComponent
                :rowData="rowData"
                :currentMonth="currentMonth"
                :currentYear="currentYear"
                :months="months"
                @cell-value-changed="onCellValueChanged"
                @grid-ready="onGridReady"
                @delete-entry="deleteEntry"
            />

            <PaginationComponent
                :currentPage="currentPage"
                :totalPages="totalPages"
                @first="onBtFirst"
                @previous="onBtPrevious"
                @next="onBtNext"
                @last="onBtLast"
            />
        </main>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import HeaderComponent from '../components/HeaderComponent.vue';
import MonthTabsComponent from '../components/MonthTabsComponent.vue';
import SummaryComponent from '../components/SummaryComponent.vue';
import CategoryPieChart from '../components/CategoryPieChart.vue';
import NewEntryFormComponent from '../components/NewEntryFormComponent.vue';
import SpreadsheetGridComponent from '../components/SpreadsheetGridComponent.vue';
import PaginationComponent from '../components/PaginationComponent.vue';
import BankStatementUploadComponent from '../components/BankStatementUploadComponent.vue';

export default {
    components: {
        HeaderComponent,
        MonthTabsComponent,
        SummaryComponent,
        CategoryPieChart,
        NewEntryFormComponent,
        SpreadsheetGridComponent,
        PaginationComponent,
        BankStatementUploadComponent
    },
    setup() {
        const months = ref(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']);
        const currentMonth = ref(months.value[new Date().getMonth()]);
        const currentYear = ref(new Date().getFullYear());
        const categories = ref([
            'Car', 'Cash Out', 'Communication', 'Divertisment', 'Education', 'Food', 'Gifts',
            'Health', 'Insurance', 'Nicotine', 'Personal', 'Rent', 'Revolut', 'Restaurant', 'Shopping',
            'Sport', 'Subscriptions', 'Supermarket', 'Transport', 'Travel', 'Utilities'
        ]);

        const rowData = ref([]);
        const gridApi = ref(null);
        const currentPage = ref(1);
        const totalPages = ref(1);

        const filteredRowData = computed(() => {
            const monthIndex = months.value.indexOf(currentMonth.value);
            return rowData.value.filter(row => {
                const rowDate = new Date(row.date);
                return rowDate.getMonth() === monthIndex && rowDate.getFullYear() === currentYear.value;
            });
        });

        const totalIncome = computed(() => {
            return filteredRowData.value
                .filter(row => row.type === 'Income')
                .reduce((sum, row) => sum + (Number(row.amount) || 0), 0);
        });

        const totalExpenses = computed(() => {
            return filteredRowData.value
                .filter(row => row.type === 'Expense')
                .reduce((sum, row) => sum + (Number(row.amount) || 0), 0);
        });

        const netAmount = computed(() => {
            return totalIncome.value - totalExpenses.value;
        });

        const totalNetAllMonths = computed(() => {
            return rowData.value.reduce((sum, row) => {
                if (row.type === 'Income') {
                    return sum + (Number(row.amount) || 0);
                } else if (row.type === 'Expense') {
                    return sum - (Number(row.amount) || 0);
                }
                return sum;
            }, 0);
        });

        const getCsrfToken = () => {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : null;
        };

        const setupAxios = () => {
            const token = getCsrfToken();
            if (token) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            } else {
                console.warn('CSRF token not found');
            }
        };

        const fetchTransactions = async () => {
            try {
                setupAxios();
                const response = await axios.get('/transactions');
                rowData.value = response.data;
                updatePaginationState();
            } catch (error) {
                console.error('Error fetching transactions:', error);
            }
        };

        const onCellValueChanged = async (event) => {
            try {
                setupAxios();
                await axios.put(`/transactions/${event.data.id}`, event.data);
                console.log('Transaction updated successfully');
            } catch (error) {
                console.error('Error updating transaction:', error);
            }
        };

        const addNewEntry = async (newEntry) => {
            try {
                setupAxios();
                const response = await axios.post('/transactions', newEntry);
                rowData.value = [...rowData.value, response.data];
                updatePaginationState();
            } catch (error) {
                console.error('Error adding new transaction:', error);
            }
        };

        const deleteEntry = async (id) => {
            if (confirm('Are you sure you want to delete this entry?')) {
                try {
                    setupAxios();
                    await axios.delete(`/transactions/${id}`);
                    rowData.value = rowData.value.filter(row => row.id !== id);
                    console.log('Transaction deleted successfully');
                    updatePaginationState();
                } catch (error) {
                    console.error('Error deleting transaction:', error);
                }
            }
        };

        const setCurrentMonth = (month) => {
            currentMonth.value = month;
            if (currentMonth.value === 'January' && months.value.indexOf(month) === 0) {
                currentYear.value++;
            } else if (currentMonth.value === 'December' && months.value.indexOf(month) === 11) {
                currentYear.value--;
            }
            updatePaginationState();
        };

        const onGridReady = (params) => {
            gridApi.value = params.api;
            updatePaginationState();
        };

        const updatePaginationState = () => {
            if (gridApi.value) {
                currentPage.value = gridApi.value.paginationGetCurrentPage() + 1;
                totalPages.value = gridApi.value.paginationGetTotalPages();
            }
        };

        const onBtFirst = () => {
            gridApi.value.paginationGoToFirstPage();
            updatePaginationState();
        };

        const onBtLast = () => {
            gridApi.value.paginationGoToLastPage();
            updatePaginationState();
        };

        const onBtNext = () => {
            gridApi.value.paginationGoToNextPage();
            updatePaginationState();
        };

        const onBtPrevious = () => {
            gridApi.value.paginationGoToPreviousPage();
            updatePaginationState();
        };

        const handleUploadedTransactions = (transactions) => {
            rowData.value = [...rowData.value, ...transactions];
            updatePaginationState();
        };

        onMounted(() => {
            fetchTransactions();
            const now = new Date();
            setCurrentMonth(months.value[now.getMonth()]);
            currentYear.value = now.getFullYear();
        });

        return {
            months,
            currentMonth,
            currentYear,
            rowData,
            filteredRowData,
            totalIncome,
            totalExpenses,
            netAmount,
            totalNetAllMonths,
            categories,
            currentPage,
            totalPages,
            onCellValueChanged,
            addNewEntry,
            deleteEntry,
            setCurrentMonth,
            onGridReady,
            onBtFirst,
            onBtLast,
            onBtNext,
            onBtPrevious,
            handleUploadedTransactions
        };
    }
};
</script>

<style>
@import "ag-grid-community/styles/ag-grid.css";
@import "ag-grid-community/styles/ag-theme-alpine.css";
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css";

/* General styles */
body {
    @apply bg-gray-100;
}

/* Input styles */
.form-input,
.form-select {
    @apply block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition duration-150 ease-in-out;
}

/* Button styles */
.btn {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out;
}

/* Custom scrollbar */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #CBD5E0 #EDF2F7;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #EDF2F7;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #CBD5E0;
    border-radius: 3px;
    border: 2px solid #EDF2F7;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #A0AEC0;
}

/* AG Grid customization */
.ag-theme-alpine {
    --ag-header-height: 40px;
    --ag-header-foreground-color: #374151;
    --ag-header-background-color: #F3F4F6;
    --ag-row-hover-color: rgba(79, 70, 229, 0.1);
    --ag-selected-row-background-color: rgba(79, 70, 229, 0.2);
    --ag-font-size: 14px;
    --ag-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

.ag-theme-alpine .ag-header-cell {
    @apply font-semibold;
}

.ag-theme-alpine .ag-cell {
    @apply py-2 px-3;
}

.ag-theme-alpine .ag-row-hover {
    @apply transition-colors duration-150 ease-in-out;
}

/* User icon styles */
.user-icon {
    @apply w-8 h-8 rounded-full flex items-center justify-center font-bold text-white cursor-pointer transition duration-150 ease-in-out;
}

.user-icon.mark {
    @apply bg-green-500 hover:bg-green-600;
}

.user-icon.roxi {
    @apply bg-red-800 hover:bg-red-900;
}

/* Delete button styles */
.delete-btn {
    @apply p-1 rounded-full hover:bg-red-100 transition duration-150 ease-in-out;
}

.delete-btn i {
    @apply text-red-500 text-sm;
}

/* Month tabs styles */
.month-tabs-container {
    @apply flex justify-center overflow-x-auto;
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.month-tabs-container::-webkit-scrollbar {
    display: none;
}

.month-tab {
    @apply px-3 py-2 text-sm font-medium rounded-md transition duration-150 ease-in-out;
}

.month-tab.active {
    @apply bg-indigo-100 text-indigo-700;
}

.month-tab:not(.active) {
    @apply text-gray-500 hover:text-gray-700;
}

/* Summary section styles */
.summary-item {
    @apply flex flex-col items-center p-4 bg-white rounded-lg shadow;
}

.summary-label {
    @apply text-sm font-medium text-gray-500 mb-1;
}

.summary-value {
    @apply text-2xl font-semibold;
}

.summary-value.income {
    @apply text-green-600;
}

.summary-value.expense {
    @apply text-red-600;
}

.summary-value.net {
    @apply text-indigo-600;
}

/* Responsive adjustments */
@media (max-width: 640px) {

    .form-input,
    .form-select,
    .btn {
        @apply text-sm;
    }

    .ag-theme-alpine {
        --ag-header-height: 36px;
        --ag-font-size: 12px;
    }

    .user-icon {
        @apply w-6 h-6 text-xs;
    }
}

/* Ensure the grid takes up the full height of its container */
.ag-theme-alpine,
.ag-root-wrapper,
.ag-root-wrapper-body,
.ag-root,
.ag-body-viewport,
.ag-center-cols-clipper {
    height: 100% !important;
}

/* Custom pagination styles */
.custom-pagination {
    @apply flex justify-center items-center gap-2 mt-4;
}

.pagination-btn {
    @apply px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition duration-150 ease-in-out;
}

.pagination-btn:disabled {
    @apply opacity-50 cursor-not-allowed;
}
</style>
