<template>
  <div class="summary-item trophy-card">
    <div class="summary-content">
      <dt class="summary-label">
        <i class="fas fa-trophy text-yellow-500 mr-2"></i>Total Net (All Months)
      </dt>
      <dd class="summary-value total-net">{{ formatCurrency(totalNet) }}</dd>
    </div>
    <div class="trophy-container">
      <div class="trophy">
        <div class="trophy-top"></div>
        <div class="trophy-body">
          <div class="liquid" :style="{ height: liquidHeight }"></div>
        </div>
        <div class="trophy-base"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  props: {
    totalNet: {
      type: Number,
      required: true
    }
  },
  setup(props) {
    const liquidHeight = computed(() => {
      const percentage = Math.min(Math.max((props.totalNet / 1000) * 100, 0), 100);
      return `${percentage}%`;
    });

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('ro-RO', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value) + ' RON';
    };

    return {
      liquidHeight,
      formatCurrency
    };
  }
}
</script>

<style scoped>
.trophy-card {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.trophy-container {
  width: 100px;
  height: 120px;
  position: relative;
  margin-top: 10px;
}

.trophy {
  width: 100%;
  height: 100%;
  position: relative;
  animation: shake 2s ease-in-out infinite;
}

.trophy-top {
  width: 60%;
  height: 20%;
  background-color: #ffd700;
  border-radius: 50% 50% 0 0;
  position: absolute;
  top: 0;
  left: 20%;
}

.trophy-body {
  width: 60%;
  height: 60%;
  background-color: #ffd700;
  position: absolute;
  top: 20%;
  left: 20%;
  overflow: hidden;
}

.liquid {
  width: 100%;
  background-color: #4a90e2;
  position: absolute;
  bottom: 0;
  left: 0;
  transition: height 0.5s ease-in-out;
  animation: wave 2s ease-in-out infinite;
}

.trophy-base {
  width: 80%;
  height: 10%;
  background-color: #ffd700;
  position: absolute;
  bottom: 0;
  left: 10%;
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(5deg); }
  75% { transform: rotate(-5deg); }
}

@keyframes wave {
  0%, 100% { transform: translateX(0); }
  50% { transform: translateX(-5px); }
}
</style>