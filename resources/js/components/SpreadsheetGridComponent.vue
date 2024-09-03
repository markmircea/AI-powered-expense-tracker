<template>
    <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">
        <div class="flex-grow custom-scrollbar" style="height: calc(100vh - 400px); min-height: 400px;">
            <ag-grid-vue class="ag-theme-alpine h-full w-full" :columnDefs="columnDefs"
                :rowData="filteredRowData" :defaultColDef="defaultColDef" :getRowStyle="getRowStyle"
                @cell-value-changed="onCellValueChanged" :pagination="true" :paginationPageSize="20"
                :suppressPaginationPanel="true" @grid-ready="onGridReady">
            </ag-grid-vue>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';

export default {
    components: {
        AgGridVue
    },
    props: {
        rowData: {
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
        }
    },
    emits: ['cell-value-changed', 'grid-ready', 'delete-entry'],
    setup(props, { emit }) {
        const gridApi = ref(null);

        const filteredRowData = computed(() => {
            const monthIndex = props.months.indexOf(props.currentMonth);
            return props.rowData.filter(row => {
                const rowDate = new Date(row.date);
                return rowDate.getMonth() === monthIndex && rowDate.getFullYear() === props.currentYear;
            });
        });

        const formatDate = (date) => {
            const d = new Date(date);
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return `${days[d.getDay()]}, ${d.toLocaleDateString()}`;
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
                    values: ['Car', 'Cash Out', 'Communication', 'Divertisment', 'Education', 'Gifts',
                        'Health', 'Insurance', 'Nicotine', 'Personal', 'Rent', 'Revolut', 'Restaurant', 'Shopping',
                        'Sport', 'Subscriptions', 'Supermarket', 'Transport', 'Travel', 'Utilities', 'Other'],
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
                        const date = new Date(props.currentYear, props.months.indexOf(props.currentMonth), 1);
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
                        emit('delete-entry', params.data.id);
                    }
                },
                sortable: false,
                filter: false,
                editable: false,
                suppressSizeToFit: true,
                cellStyle: { padding: '0' }
            }
        ]);

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

        const getRowStyle = (params) => {
            if (params.data.type === 'Income') {
                return { background: 'rgba(230, 255, 237, 0.5)' };
            } else if (params.data.type === 'Expense') {
                return { background: 'rgba(255, 235, 238, 0.5)' };
            }
            return null;
        };

        const onCellValueChanged = (event) => {
            emit('cell-value-changed', event);
        };

        const onGridReady = (params) => {
            gridApi.value = params.api;
            emit('grid-ready', params);
        };

        return {
            columnDefs,
            filteredRowData,
            defaultColDef,
            getRowStyle,
            onCellValueChanged,
            onGridReady
        };
    }
}
</script>