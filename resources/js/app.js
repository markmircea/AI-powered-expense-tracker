import './bootstrap';
import { createApp } from 'vue'
import SpreadSheetComponent from './components/SpreadSheetComponent.vue'

import 'ag-grid-community/styles/ag-grid.css'
import 'ag-grid-community/styles/ag-theme-alpine.css'

const app = createApp({})

app.component('spreadsheet-component', SpreadSheetComponent)

app.mount('#app')
