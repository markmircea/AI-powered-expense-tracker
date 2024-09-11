<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <HeaderComponent :currentMonth="currentMonth" :currentYear="currentYear" :auth="auth" />
        <main class="flex-grow p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Error message display -->
                <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline">{{ errorMessage }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="clearError">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>

                <MonthTabsComponent :months="months" :currentMonth="currentMonth" @update:currentMonth="setCurrentMonth"
                    data-aos="fade-down" />

                <select
                    id="team-select"
                    v-model="selectedTeamId"
                    @change="fetchTransactions"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                    <option :value="null">Personal Transactions</option>
                    <optgroup label="Teams I Own">
                        <option v-for="team in ownedTeams" :key="'owned-' + team.id" :value="team.id">
                            {{ team.name }} (Owner)
                        </option>
                    </optgroup>
                    <optgroup label="Teams I'm a Member Of">
                        <option v-for="team in filteredMemberTeams" :key="'member-' + team.id" :value="team.id">
                            {{ team.name }}
                        </option>
                    </optgroup>
                </select>

                <SummaryComponent :totalIncome="totalIncome" :totalExpenses="totalExpenses" :netAmount="netAmount"
                    :totalNetAllMonths="totalNetAllMonths" data-aos="fade-up" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="bg-white rounded-lg shadow p-6" data-aos="fade-right">
                        <h2 class="text-lg font-semibold mb-4">Expense Categories for {{ currentMonth }} {{ currentYear }}</h2>
                        <CategoryPieChart :transactions="filteredRowData" :currentMonth="months.indexOf(currentMonth)"
                            :currentYear="currentYear" />
                    </div>

                    <div class="bg-white rounded-lg shadow p-6" data-aos="fade-left">
                        <h2 class="text-lg font-semibold mb-4">Upload Bank Statement</h2>
                        <BankStatementUploadComponent
  @transactions-updated="handleUploadedTransactions"
  :selectedTeamId="selectedTeamId"
/>                    </div>
                </div>

                <NewEntryFormComponent :auth="auth" :categories="categories" :currentMonth="currentMonth" :currentYear="currentYear"
                    :months="months" :selectedTeamId="selectedTeamId" @add-new-entry="addNewEntry" class="mt-6"
                    data-aos="fade-up" />

                <div class="mt-6" data-aos="fade-up">
                    <SpreadsheetGridComponent :rowData="rowData" :currentMonth="currentMonth" :currentYear="currentYear"
                        :months="months" @cell-value-changed="onCellValueChanged" @grid-ready="onGridReady"
                        @delete-entries="deleteEntries"
                        />


                </div>

                <PaginationComponent :currentPage="currentPage" :totalPages="totalPages" @first="onBtFirst"
                    @previous="onBtPrevious" @next="onBtNext" @last="onBtLast" class="mt-4" data-aos="fade-up" />
            </div>
        </main>
    </div>
</template>
<script>
import { ref, provide } from 'vue';
import { usePage } from '@inertiajs/vue3';
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
        const selectedTeamId = ref(null);
        provide('selectedTeamId', selectedTeamId);

        return {
            selectedTeamId,
        };
    },
    data() {
        return {
            auth: null,
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            currentMonth: '',
            currentYear: new Date().getFullYear(),
            categories: [
                'Car', 'Cash Out', 'Communication', 'Divertisment', 'Education', 'Food', 'Gifts',
                'Health', 'Insurance', 'Nicotine', 'Personal', 'Rent', 'Revolut', 'Restaurant', 'Shopping',
                'Sport', 'Subscriptions', 'Supermarket', 'Transport', 'Travel', 'Utilities'
            ],
            rowData: [],
            gridApi: null,
            currentPage: 1,
            totalPages: 1,
            ownedTeams: [],
            memberTeams: [],
            errorMessage: null,
        };
    },
    computed: {
        filteredMemberTeams() {
            return this.memberTeams.filter(team => !this.ownedTeams.some(ownedTeam => ownedTeam.id === team.id));
        },
        allTeams() {
            return [...this.ownedTeams, ...this.memberTeams];
        },
        filteredRowData() {
            const monthIndex = this.months.indexOf(this.currentMonth);
            return this.rowData.filter(row => {
                const rowDate = new Date(row.date);
                return rowDate.getMonth() === monthIndex && rowDate.getFullYear() === this.currentYear;
            });
        },
        totalIncome() {
            return this.filteredRowData
                .filter(row => row.type === 'Income')
                .reduce((sum, row) => sum + (Number(row.amount) || 0), 0);
        },
        totalExpenses() {
            return this.filteredRowData
                .filter(row => row.type === 'Expense')
                .reduce((sum, row) => sum + (Number(row.amount) || 0), 0);
        },
        netAmount() {
            return this.totalIncome - this.totalExpenses;
        },
        totalNetAllMonths() {
            return this.rowData.reduce((sum, row) => {
                if (row.type === 'Income') {
                    return sum + (Number(row.amount) || 0);
                } else if (row.type === 'Expense') {
                    return sum - (Number(row.amount) || 0);
                }
                return sum;
            }, 0);
        },
    },
    methods: {
        async fetchUserTeams() {
            try {
                const response = await axios.get('/user/teams');
                console.log('Full API Response:', response);

                if (response.data && response.data.ownedTeams && response.data.memberTeams) {
                    this.ownedTeams = response.data.ownedTeams;
                    this.memberTeams = response.data.memberTeams;
                    console.log('Owned Teams set:', this.ownedTeams);
                    console.log('Member Teams set:', this.memberTeams);
                } else {
                    console.error('Unexpected response structure:', response.data);
                    this.showError('Unexpected response structure when fetching teams.');
                }
            } catch (error) {
                console.error('Error fetching user teams:', error);
                this.handleAxiosError(error, 'Error fetching user teams');
            }
        },
        getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : null;
        },
        setupAxios() {
            const token = this.getCsrfToken();
            if (token) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            } else {
                console.warn('CSRF token not found');
                this.showError('CSRF token not found. Please refresh the page and try again.');
            }
        },
        async fetchTransactions() {
            try {
                this.setupAxios();
                const response = await axios.get('/transactions', {
                    params: {
                        month: this.months.indexOf(this.currentMonth) + 1,
                        year: this.currentYear,
                        team_id: this.selectedTeamId,
                    },
                });
                console.log('Fetched transactions:', response.data);
                this.rowData = response.data;
                console.log('Updated rowData:', this.rowData);
                this.updatePaginationState();
            } catch (error) {
                console.error('Error fetching transactions:', error);
                this.handleAxiosError(error, 'Error fetching transactions');
            }
        },
        async onCellValueChanged(event) {
            try {
                this.setupAxios();
                await axios.put(`/transactions/${event.data.id}`, {
                    ...event.data,
                    team_id: this.selectedTeamId
                });
                console.log('Transaction updated successfully');
            } catch (error) {
                console.error('Error updating transaction:', error);
                this.handleAxiosError(error, 'Error updating transaction');
            }
        },
        async addNewEntry(newEntry) {
            try {
                this.setupAxios();
                newEntry.team_id = this.selectedTeamId;
                const response = await axios.post('/transactions', newEntry);
                this.rowData = [...this.rowData, response.data];
                this.updatePaginationState();
            } catch (error) {
                console.error('Error adding new transaction:', error);
                this.handleAxiosError(error, 'Error adding new transaction');
            }
        },
        async deleteEntries(ids) {
      if (confirm(`Are you sure you want to delete ${ids.length} entries?`)) {
        try {
          this.setupAxios();
          await axios.post('/transactions/delete-multiple', { ids });
          this.rowData = this.rowData.filter(row => !ids.includes(row.id));
          console.log('Transactions deleted successfully');
          this.updatePaginationState();
        } catch (error) {
          console.error('Error deleting transactions:', error);
          this.handleAxiosError(error, 'Error deleting transactions');
        }
      }
    },
        setCurrentMonth(month) {
            this.currentMonth = month;
            if (this.currentMonth === 'January' && this.months.indexOf(month) === 0) {
                this.currentYear++;
            } else if (this.currentMonth === 'December' && this.months.indexOf(month) === 11) {
                this.currentYear--;
            }
            this.fetchTransactions();
        },
        onGridReady(params) {
            this.gridApi = params.api;
            this.updatePaginationState();
        },
        updatePaginationState() {
            if (this.gridApi) {
                this.currentPage = this.gridApi.paginationGetCurrentPage() + 1;
                this.totalPages = this.gridApi.paginationGetTotalPages();
            }
        },
        onBtFirst() {
            this.gridApi.paginationGoToFirstPage();
            this.updatePaginationState();
        },
        onBtLast() {
            this.gridApi.paginationGoToLastPage();
            this.updatePaginationState();
        },
        onBtNext() {
            this.gridApi.paginationGoToNextPage();
            this.updatePaginationState();
        },
        onBtPrevious() {
            this.gridApi.paginationGoToPreviousPage();
            this.updatePaginationState();
        },
        handleUploadedTransactions(transactions) {
            this.rowData = [...this.rowData, ...transactions];
            this.updatePaginationState();
        },
        showError(message) {
            this.errorMessage = message;
            setTimeout(() => {
                this.clearError();
            }, 5000);
        },
        clearError() {
            this.errorMessage = null;
        },
        handleAxiosError(error, defaultMessage) {
            if (error.response) {
                this.showError(`${defaultMessage}: ${error.response.data.message || error.response.statusText}`);
            } else if (error.request) {
                this.showError(`${defaultMessage}: No response received from server. Please check your internet connection.`);
            } else {
                this.showError(`${defaultMessage}: ${error.message}`);
            }
        },
    },
    created() {
        console.log('Component created');
        this.fetchUserTeams();
    },
    mounted() {
        console.log('Component mounted');
        if (this.ownedTeams.length === 0 && this.memberTeams.length === 0) {
            console.log('Teams not loaded in created hook, fetching again');
            this.fetchUserTeams();
        }
        const page = usePage();
        this.auth = page.props.auth;
        this.fetchUserTeams();
        this.fetchTransactions();
        const now = new Date();
        this.setCurrentMonth(this.months[now.getMonth()]);
        this.currentYear = now.getFullYear();
        console.log('SpreadsheetComponent mounted, auth:', this.auth);
    },
    watch: {
        auth: {
            handler(newAuth) {
                console.log('Auth changed:', newAuth);
            },
            deep: true
        },
        selectedTeamId: {
            handler(newValue) {
                console.log('Selected Team ID changed:', newValue);
                this.fetchTransactions();
            },
            immediate: true
        }
    },
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
    @apply block w-full px-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition duration-300 ease-in-out;
}

/* Button styles */
.btn {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300 ease-in-out;
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
    @apply transition-colors duration-300 ease-in-out;
}

/* User icon styles */
.user-icon {
    @apply w-8 h-8 rounded-full flex items-center justify-center font-bold text-white cursor-pointer transition duration-300 ease-in-out;
}

.user-icon.mark {
    @apply bg-green-500 hover:bg-green-600;
}

.user-icon.roxi {
    @apply bg-red-800 hover:bg-red-900;
}

/* Delete button styles */
.delete-btn {
    @apply p-1 rounded-full hover:bg-red-100 transition duration-300 ease-in-out;
}

.delete-btn i {
    @apply text-red-500 text-sm;
}

/* Month tabs styles */
.month-tabs-container {
    @apply flex justify-center overflow-x-auto;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.month-tabs-container::-webkit-scrollbar {
    display: none;
}

.month-tab {
    @apply px-3 py-2 text-sm font-medium rounded-md transition duration-300 ease-in-out;
}

.month-tab.active {
    @apply bg-indigo-100 text-indigo-700;
}

.month-tab:not(.active) {
    @apply text-gray-500 hover:text-gray-700;
}

/* Summary section styles */
.summary-item {
    @apply flex flex-col items-center p-4 bg-white rounded-lg shadow transition duration-300 ease-in-out hover:shadow-lg;
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
    @apply px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition duration-300 ease-in-out;
}

.pagination-btn:disabled {
    @apply opacity-50 cursor-not-allowed;
}

/* Animation classes */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.8s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(20px);
    opacity: 0;
}

.bounce-enter-active {
    animation: bounce-in 0.5s;
}

.bounce-leave-active {
    animation: bounce-in 0.5s reverse;
}

@keyframes bounce-in {
    0% {
        transform: scale(0);
    }

    50% {
        transform: scale(1.25);
    }

    100% {
        transform: scale(1);
    }
}
</style>
