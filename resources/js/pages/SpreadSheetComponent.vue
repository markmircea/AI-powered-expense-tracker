<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <HeaderComponent :currentMonth="currentMonth" :currentYear="currentYear" :auth="auth" />
        <main class="flex-grow p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">




                <!-- Flash Messages -->
                <div class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-96 z-50">
                    <transition
                        name="flash"
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div v-if="errorMessage" class="flash-message error flex items-start justify-between mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md" role="alert">
                            <div class="flex-grow mr-2">
                                <p><strong class="font-bold">Error:</strong> {{ errorMessage }}</p>
                            </div>
                            <button @click="clearError" class="flex-shrink-0">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </transition>

                    <transition name="flash">
                        <div v-if="successMessage" class="flash-message success flex items-start justify-between mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg shadow-md" role="alert">
                            <div class="flex-grow mr-2">
                                <p><strong class="font-bold">Success:</strong> {{ successMessage }}</p>
                            </div>
                            <button @click="clearSuccess" class="flex-shrink-0">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </transition>
                </div>




                <MonthTabsComponent :months="months" :currentMonth="currentMonth" @update:currentMonth="setCurrentMonth"
                    class="animate-fadeIn" data-aos="fade-down" />

                <div class="glass rounded-lg p-4 animate-fadeIn" data-aos="fade-down">
                    <div class="relative">
                        <select id="team-select" v-model="selectedTeamId" @change="fetchTransactions"
                            class="block w-full pl-4 pr-10 py-3 text-base border-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm rounded-lg transition-all duration-300 appearance-none bg-white shadow-md hover:shadow-lg">
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
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>


                <SummaryComponent :totalIncome="totalIncome" :totalExpenses="totalExpenses" :netAmount="netAmount"
                    :totalNetAllMonths="totalNetAllMonths" class="animate-fadeIn" data-aos="fade-in" />

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="animate-fadeIn">
                        <CategoryPieChart :transactions="filteredRowData" :currentMonth="months.indexOf(currentMonth)"
                            :currentYear="currentYear" :currentMonthText="currentMonth" data-aos="fade-right" />
                    </div>

                    <BankStatementUploadComponent @transactions-updated="handleUploadedTransactions"
                        :selectedTeamId="selectedTeamId" class="animate-fadeIn" data-aos="fade-left" />
                </div>

                <NewEntryFormComponent :auth="auth" :categories="categories" :currentMonth="currentMonth"
                    :currentYear="currentYear" :months="months" :selectedTeamId="selectedTeamId"
                    @add-new-entry="addNewEntry" class="animate-fadeIn" data-aos="fade-up" />

                <div class="">
                    <SpreadsheetGridComponent :rowData="rowData" :currentMonth="currentMonth" :currentYear="currentYear"
                        :months="months" @cell-value-changed="onCellValueChanged" @grid-ready="onGridReady"
                        @delete-entries="deleteEntries" data-aos="fade-up" />
                </div>

                <PaginationComponent :currentPage="currentPage" :totalPages="totalPages" @first="onBtFirst"
                    @previous="onBtPrevious" @next="onBtNext" @last="onBtLast" class="animate-fadeIn"
                    data-aos="fade-up" />
            </div>
        </main>
    </div>
</template>

<script>
import { ref, provide, computed } from 'vue';
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

        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const currentMonth = ref(months[new Date().getMonth()]);
        const currentYear = ref(new Date().getFullYear());
        const categories = [
            'Car', 'Cash Out', 'Communication', 'Divertisment', 'Education', 'Food', 'Gifts',
            'Health', 'Insurance', 'Nicotine', 'Personal', 'Rent', 'Revolut', 'Restaurant', 'Shopping',
            'Sport', 'Subscriptions', 'Supermarket', 'Transport', 'Travel', 'Utilities'
        ];
        const rowData = ref([]);
        const gridApi = ref(null);
        const currentPage = ref(1);
        const totalPages = ref(1);
        const ownedTeams = ref([]);
        const memberTeams = ref([]);
        const errorMessage = ref(null);
        const successMessage = ref(null);

        const filteredMemberTeams = computed(() => {
            return memberTeams.value.filter(team => !ownedTeams.value.some(ownedTeam => ownedTeam.id === team.id));
        });

        const filteredRowData = computed(() => {
            const monthIndex = months.indexOf(currentMonth.value);
            return rowData.value.filter(row => {
                const rowDate = new Date(row.date);
                return rowDate.getMonth() === monthIndex && rowDate.getFullYear() === currentYear.value;
            });
        });

        const totalIncome = computed(() => {
            return filteredRowData.value
                .filter(row => row.type === 'Income')
                .reduce((sum, row) => sum + Math.abs(Number(row.amount) || 0), 0);
        });

        const totalExpenses = computed(() => {
            return filteredRowData.value
                .filter(row => row.type === 'Expense')
                .reduce((sum, row) => sum + Math.abs(Number(row.amount) || 0), 0);
        });

        const netAmount = computed(() => {
            return totalIncome.value - totalExpenses.value;
        });

        const totalNetAllMonths = computed(() => {
            return rowData.value.reduce((sum, row) => {
                if (row.type === 'Income') {
                    return sum + Math.abs(Number(row.amount) || 0);
                } else if (row.type === 'Expense') {
                    return sum - Math.abs(Number(row.amount) || 0);
                }
                return sum;
            }, 0);
        });

        return {
            selectedTeamId,
            months,
            currentMonth,
            currentYear,
            categories,
            rowData,
            gridApi,
            currentPage,
            totalPages,
            ownedTeams,
            memberTeams,
            errorMessage,
            successMessage,
            filteredMemberTeams,
            filteredRowData,
            totalIncome,
            totalExpenses,
            netAmount,
            totalNetAllMonths
        };
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
                this.rowData = response.data.data;
                this.showSuccess(response.data.message);
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
                const response = await axios.put(`/transactions/${event.data.id}`, {
                    ...event.data,
                    team_id: this.selectedTeamId
                });
                console.log('Transaction updated successfully');
                this.showSuccess(response.data.message);
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
                this.rowData = [...this.rowData, response.data.data];
                this.showSuccess(response.data.message);
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
                    const response = await axios.post('/transactions/delete-multiple', { ids });
                    console.log('Full response:', response);
                    console.log('Response data:', response.data);
                    this.rowData = this.rowData.filter(row => !ids.includes(row.id));
                    this.showSuccess(response.data.message);
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
        showSuccess(message) {
            this.successMessage = message;
            setTimeout(() => {
                this.clearSuccess();
            }, 5000);
        },
        clearSuccess() {
            this.successMessage = null;
        },
        handleAxiosError(error, defaultMessage) {
            if (error.response) {
                this.showError(`${defaultMessage}: ${error.response.data.error || error.response.statusText}`);
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


<style scoped>
.flash-message {
    @apply py-3 px-4 shadow-md;
    transition: all 0.5s ease;
}

.flash-message.error {
    @apply bg-red-500 text-white;
}

.flash-message.success {
    @apply bg-green-500 text-white;
}

.flash-enter-active, .flash-leave-active {
    transition: all 0.5s ease;
}

.flash-enter-from, .flash-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}


</style>
