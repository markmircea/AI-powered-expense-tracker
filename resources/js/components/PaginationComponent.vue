<template>
    <div class="custom-pagination p-4 bg-gray-50 border-t border-gray-200">
        <button @click="onBtFirst" class="pagination-btn" :disabled="currentPage === 1">
            <i class="fas fa-angle-double-left"></i>
        </button>
        <button @click="onBtPrevious" class="pagination-btn" :disabled="currentPage === 1">
            <i class="fas fa-angle-left"></i>
        </button>
        <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="onBtNext" class="pagination-btn" :disabled="currentPage === totalPages">
            <i class="fas fa-angle-right"></i>
        </button>
        <button @click="onBtLast" class="pagination-btn" :disabled="currentPage === totalPages">
            <i class="fas fa-angle-double-right"></i>
        </button>
    </div>
</template>

<script>
export default {
    props: {
        currentPage: {
            type: Number,
            required: true
        },
        totalPages: {
            type: Number,
            required: true
        }
    },
    emits: ['first', 'previous', 'next', 'last'],
    setup(props, { emit }) {
        const onBtFirst = () => {
            emit('first');
        };

        const onBtPrevious = () => {
            emit('previous');
        };

        const onBtNext = () => {
            emit('next');
        };

        const onBtLast = () => {
            emit('last');
        };

        return {
            onBtFirst,
            onBtPrevious,
            onBtNext,
            onBtLast
        };
    }
}
</script>