<template>
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
</template>

<script>
import { ref, onMounted, nextTick } from 'vue';

export default {
    props: {
        months: {
            type: Array,
            required: true
        },
        currentMonth: {
            type: String,
            required: true
        }
    },
    emits: ['update:currentMonth'],
    setup(props, { emit }) {
        const monthTabsContainer = ref(null);
        const currentMonthRef = ref(null);

        const setCurrentMonth = (month) => {
            emit('update:currentMonth', month);
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

        onMounted(() => {
            if (currentMonthRef.value && monthTabsContainer.value) {
                const container = monthTabsContainer.value;
                const tab = currentMonthRef.value;
                const containerWidth = container.offsetWidth;
                const tabWidth = tab.offsetWidth;
                const scrollLeft = tab.offsetLeft - (containerWidth / 2) + (tabWidth / 2);
                container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
            }
        });

        return {
            monthTabsContainer,
            currentMonthRef,
            setCurrentMonth
        };
    }
}
</script>