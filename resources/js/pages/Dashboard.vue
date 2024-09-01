<template>
  <div class="dashboard">
    <h1>Financial Planner</h1>
    <FinancialSheet />
  </div>
</template>

<script>
import FinancialSheet from '../components/FinancialSheet.vue';

export default {
  name: 'Dashboard',
  components: {
    FinancialSheet
  }
}
</script>

<style scoped>
.dashboard {
  padding: 20px;
}
</style>