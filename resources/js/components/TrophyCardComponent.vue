<template>
  <div class="summary-item trophy-card p-6">
    <div class="summary-content mb-4">
      <dt class="summary-label text-lg font-semibold mb-2 gradient-text">
        <i class="fas fa-trophy text-yellow-500 mr-2"></i>Total Net (All Months)
      </dt>
      <dd class="summary-value total-net text-2xl font-bold" :class="totalNet >= 0 ? 'text-green-600' : 'text-red-600'">
        {{ formatCurrency(totalNet) }}
      </dd>
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
      const percentage = Math.min(Math.max((props.totalNet / 10000) * 100, 0), 100);
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
  @apply flex flex-col items-center justify-between h-full;
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
  animation: float 3s ease-in-out infinite;
}

.trophy-top {
  width: 60%;
  height: 20%;
  background: linear-gradient(45deg, #ffd700, #ffec8b);
  border-radius: 50% 50% 0 0;
  position: absolute;
  top: 0;
  left: 20%;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.trophy-body {
  width: 60%;
  height: 60%;
  background: linear-gradient(45deg, #ffd700, #ffec8b);
  position: absolute;
  top: 20%;
  left: 20%;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.liquid {
  width: 100%;
  background: linear-gradient(45deg, #4a90e2, #63b3ed);
  position: absolute;
  bottom: 0;
  left: 0;
  transition: height 0.5s ease-in-out;
  animation: wave 2s ease-in-out infinite;
}

.trophy-base {
  width: 80%;
  height: 10%;
  background: linear-gradient(45deg, #ffd700, #ffec8b);
  position: absolute;
  bottom: 0;
  left: 10%;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

@keyframes wave {
  0%, 100% { transform: translateX(0) scaleY(1); }
  50% { transform: translateX(-5px) scaleY(1.02); }
}

@keyframes shine {
  0% { background-position: -100px; }
  40%, 100% { background-position: 140px; }
}

.trophy::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    to right,
    rgba(255, 255, 255, 0) 0%,
    rgba(255, 255, 255, 0.3) 50%,
    rgba(255, 255, 255, 0) 100%
  );
  transform: rotate(30deg);
  animation: shine 4s infinite;
}

@media (max-width: 640px) {
  .trophy-container {
    width: 80px;
    height: 96px;
  }
}
</style>
