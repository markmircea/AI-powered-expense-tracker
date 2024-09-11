<template>
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
</template>

<script>
import { ref, computed } from 'vue';

export default {
    props: {
        categories: {
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
        },
        currentUserId: {
            type: Number,
            required: true
        },
        selectedTeamId: {
            type: Number,
            required: false,
            default: null
        },
        auth: Object
    },
    emits: ['add-new-entry'],
    setup(props, { emit }) {
        const isNewEntryFormExpanded = ref(false);
        const isMobile = ref(false);

        const newEntry = ref({
            user: '',
            description: '',
            amount: null,
            type: '',
            category: '',
            date: computed(() => new Date(props.currentYear, props.months.indexOf(props.currentMonth), 1).toISOString().split('T')[0]),
            user_id: props.currentUserId,
            team_id: props.selectedTeamId
        });

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

        const addNewEntry = () => {
            emit('add-new-entry', { ...newEntry.value });
            newEntry.value = {
                user: '',
                description: '',
                amount: null,
                type: '',
                category: '',
                date: new Date(props.currentYear, props.months.indexOf(props.currentMonth), 1).toISOString().split('T')[0],
                user_id: props.auth.user.id,
                team_id: props.selectedTeamId
            };
        };

        return {
            newEntry,
            isNewEntryFormExpanded,
            isMobile,
            toggleNewEntryForm,
            onEnter,
            onLeave,
            addNewEntry
        };
    }
}
</script>
