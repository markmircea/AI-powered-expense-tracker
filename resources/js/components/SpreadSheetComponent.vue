<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Financial Planner</h1>
                <div class="text-gray-500">
                    <i class="fas fa-calendar-alt mr-2"></i>{{ currentMonth }} {{ currentYear }}
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-grow p-4 sm:p-6 lg:p-8">
            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
                <div class="month-tabs-container custom-scrollbar" ref="monthTabsContainer">
                    <nav class="flex p-2" aria-label="Tabs">
                        <a v-for="month in months" :key="month"
                            :class="['month-tab', month === currentMonth ? 'active' : '']"
                            @click="setCurrentMonth(month)"
                            :ref="el => { if (month === currentMonth) currentMonthRef = el }">
                            <i class="fas fa-calendar-day mr-2"></i>{{ month }}
                        </a>
                    </nav>
                </div>
            </div>


            <!-- Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 mt-6">
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
            </div>


            <!-- Category Pie Chart -->
            <div class="bg-white rounded-lg shadow mt-6 mb-6 p-4">
                <h2 class="text-lg font-semibold mb-4">Expense Categories for {{ currentMonth }} {{ currentYear }}</h2>
                <CategoryPieChart :transactions="filteredRowData" :currentMonth="months.indexOf(currentMonth)"
                    :currentYear="currentYear" />
            </div>

            <!-- New Entry Form -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <h2 @click="toggleNewEntryForm" class="text-lg font-semibold mb-4 flex items-center cursor-pointer">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Add New Entry
                    <i
                        :class="['fas', 'ml-2', 'transition-transform', 'duration-300', isNewEntryFormExpanded ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
                </h2>
                <transition enter-active-class="transition-all duration-300 ease-out"
                    leave-active-class="transition-all duration-300 ease-in" @enter="onEnter" @leave="onLeave">
                    <form v-show="isNewEntryFormExpanded || !isMobile" @submit.prevent="addNewEntry"
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
                        <div class="flex items-center space-x-4">
                            <button type="button" @click="newEntry.user = 'Mark'"
                                :class="['user-icon', newEntry.user === 'Mark' ? 'mark' : 'bg-gray-300']">
                                M
                            </button>
                            <button type="button" @click="newEntry.user = 'Roxi'"
                                :class="['user-icon', newEntry.user === 'Roxi' ? 'roxi' : 'bg-gray-300']">
                                R
                            </button>
                        </div>
                        <input v-model="newEntry.description" placeholder="Description"
                            class="form-input rounded-md shadow-sm" required>
                        <input v-model.number="newEntry.amount" type="number" placeholder="Amount"
                            class="form-input rounded-md shadow-sm" required>
                        <select v-model="newEntry.type" class="form-select rounded-md shadow-sm" required>
                            <option value="">Select Type</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                        <div class="relative">
                            <select v-model="newEntry.category" class="form-select rounded-md shadow-sm w-full"
                                required>
                                <option value="">Select or type Category</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                            <input v-model="newEntry.category" placeholder="Custom Category"
                                class="form-input rounded-md shadow-sm mt-2 w-full"
                                v-if="!categories.includes(newEntry.category)">
                        </div>
                        <input v-model="newEntry.date" type="date" class="form-input rounded-md shadow-sm" required>
                        <button type="submit" class="btn">
                            <i class="fas fa-save mr-2"></i>Add Entry
                        </button>
                    </form>
                </transition>
            </div>

            <!-- Spreadsheet -->
            <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
                <div class="flex-grow custom-scrollbar" style="height: calc(100vh - 400px); min-height: 400px;">
                    <ag-grid-vue class="ag-theme-alpine h-full w-full" :columnDefs="columnDefs"
                        :rowData="filteredRowData" :defaultColDef="defaultColDef" :getRowStyle="getRowStyle"
                        @cell-value-changed="onCellValueChanged" :pagination="true" :paginationPageSize="20"
                        :suppressPaginationPanel="true">
                    </ag-grid-vue>
                </div>
                <div class="custom-pagination p-4 bg-gray-50 border-t border-gray-200">
                    <button @click="onBtFirst" class="pagination-btn"><i class="fas fa-angle-double-left"></i></button>
                    <button @click="onBtPrevious" class="pagination-btn"><i class="fas fa-angle-left"></i></button>
                    <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button @click="onBtNext" class="pagination-btn"><i class="fas fa-angle-right"></i></button>
                    <button @click="onBtLast" class="pagination-btn"><i class="fas fa-angle-double-right"></i></button>
                </div>
            </div>



        </main>
    </div>
</template>
<script>
import { ref, computed, onMounted, nextTick } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import axios from 'axios';
import CategoryPieChart from './CategoryPieChart.vue';

export default {
    components: {
        AgGridVue,
        CategoryPieChart
    },
    setup() {
        const months = ref(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']);
        const currentMonth = ref(months.value[new Date().getMonth()]);
        const currentYear = ref(new Date().getFullYear());
        const categories = ref([
            'Car', 'Cash Out', 'Communication', 'Divertisment', 'Education', 'Gifts',
            'Health', 'Insurance', 'Nicotine', 'Personal', 'Rent', 'Revolut', 'Restaurant', 'Shopping',
            'Sport', 'Subscriptions', 'Supermarket', 'Transport', 'Travel', 'Utilities'
        ]);
        const monthTabsContainer = ref(null);
        const currentMonthRef = ref(null);

        const formatDate = (date) => {
            const d = new Date(date);
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return `${days[d.getDay()]}, ${d.toLocaleDateString()}`;
        };

        const isNewEntryFormExpanded = ref(false);
        const isMobile = ref(false);

        const checkMobile = () => {
            isMobile.value = window.innerWidth < 640;
        };

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

        const getRowStyle = (params) => {
            if (params.data.type === 'Income') {
                return { background: 'rgba(230, 255, 237, 0.5)' };
            } else if (params.data.type === 'Expense') {
                return { background: 'rgba(255, 235, 238, 0.5)' };
            }
            return null;
        };

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('ro-RO', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value) + ' RON';
        };

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

        const columnDefs = ref([
            {
                headerName: "",
                field: "user",
                editable: true,
                cellRenderer: params => {
                    const value = params.value.toLowerCase();
                    const icon = value === 'mark' ? 'M' : 'R';
                    const className = value === 'mark' ? 'mark' : 'roxi';
                    return `<div class="flex items-center justify-center h-full">
                    <span class="user-icon ${className}">${icon}</span>
                  </div>`;
                },
                cellEditor: 'agSelectCellEditor',
                cellEditorParams: {
                    values: ['Mark', 'Roxi', 'Roxy']
                },
                valueFormatter: params => {
                    return params.value.toLowerCase() === 'roxy' ? 'Roxi' : params.value;
                },
                width: 60,
                maxWidth: 60,
                minWidth: 60,
                resizable: false,
                suppressSizeToFit: true,
                cellStyle: { padding: '0' }
            },
            { headerName: "Description", field: "description", editable: true, minWidth: 200 },
            {
                headerName: "Amount",
                field: "amount",
                editable: true,
                valueParser: params => Number(params.newValue),
                valueFormatter: params => formatCurrency(params.value),
                minWidth: 120
            },
            {
                headerName: "Type",
                field: "type",
                editable: true,
                cellEditor: 'agSelectCellEditor',
                cellEditorParams: {
                    values: ['Income', 'Expense']
                },
                cellRenderer: params => {
                    const icon = params.value === 'Income' ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600';
                    return `<i class="fas ${icon} mr-2"></i>${params.value}`;
                },
                minWidth: 100
            },
            {
                headerName: "Category",
                field: "category",
                editable: true,
                cellEditor: 'agSelectCellEditor',
                cellEditorParams: {
                    values: categories.value.concat(['Other']),
                },
                cellRenderer: params => {
                    const icon = getCategoryIcon(params.value);
                    return `<i class="fas ${icon} mr-2"></i>${params.value}`;
                },
                minWidth: 150
            },
            {
                headerName: "Date",
                field: "date",
                editable: true,
                valueFormatter: (params) => formatDate(params.value),
                cellEditor: 'datePicker',
                cellEditorParams: {
                    defaultDate: () => {
                        const date = new Date(currentYear.value, months.value.indexOf(currentMonth.value), 1);
                        return date.toISOString().split('T')[0];
                    }
                },
                cellEditorPopup: true,
                minWidth: 150
            },
            {
                headerName: "",
                field: "id",
                width: 40,
                maxWidth: 40,
                minWidth: 40,
                cellRenderer: params => {
                    return `<div class="h-full w-full flex items-center justify-center">
                <button class="delete-btn" data-id="${params.value}">
                  <i class="fas fa-trash-alt text-red-500"></i>
                </button>
              </div>`;
                },
                onCellClicked: params => {
                    if (params.event.target.classList.contains('delete-btn') ||
                        params.event.target.closest('.delete-btn')) {
                        deleteEntry(params.data.id);
                    }
                },
                sortable: false,
                filter: false,
                editable: false,
                suppressSizeToFit: true,
                cellStyle: { padding: '0' }
            }
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

        const defaultColDef = {
            flex: 1,
            editable: true,
            resizable: true,
            sortable: true,
            filter: true,
            minWidth: 100,
            autoHeight: true,
            wrapText: true
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

        const newEntry = ref({
            user: '',
            description: '',
            amount: null,
            type: '',
            category: '',
            date: new Date().toISOString().split('T')[0]
        });

        const addNewEntry = async () => {
            try {
                setupAxios();
                const response = await axios.post('/transactions', newEntry.value);
                rowData.value = [...rowData.value, response.data];
                newEntry.value = {
                    user: '',
                    description: '',
                    amount: null,
                    type: '',
                    category: '',
                    date: new Date(currentYear.value, months.value.indexOf(currentMonth.value), 1).toISOString().split('T')[0]
                };
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
            newEntry.value.date = new Date(currentYear.value, months.value.indexOf(currentMonth.value), 1).toISOString().split('T')[0];
            updatePaginationState();

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

        onMounted(() => {
            fetchTransactions();
            const now = new Date();
            setCurrentMonth(months.value[now.getMonth()]);
            currentYear.value = now.getFullYear();
            checkMobile();
            window.addEventListener('resize', checkMobile);
        });

        return {
            months,
            currentMonth,
            currentYear,
            columnDefs,
            rowData,
            filteredRowData,
            defaultColDef,
            onCellValueChanged,
            totalIncome,
            totalExpenses,
            netAmount,
            newEntry,
            addNewEntry,
            deleteEntry,
            setCurrentMonth,
            getRowStyle,
            formatCurrency,
            categories,
            onGridReady,
            onBtFirst,
            onBtLast,
            onBtNext,
            onBtPrevious,
            currentPage,
            totalPages,
            isNewEntryFormExpanded,
            isMobile,
            toggleNewEntryForm,
            onEnter,
            onLeave,
            monthTabsContainer,
            currentMonthRef,
            CategoryPieChart
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
