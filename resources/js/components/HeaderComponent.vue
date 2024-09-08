<template>
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Financial Planner</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li>
                        <a href="/" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    </li>
                    <li>
                        <a href="/upload-bank-statement" class="text-gray-600 hover:text-gray-900">Upload Bank Statement</a>
                    </li>
                </ul>
            </nav>
            <div class="text-gray-500">
                <i class="fas fa-calendar-alt mr-2"></i>{{ currentMonth }} {{ currentYear }}
            </div>
        </div>
    </header>
</template>

<script>
export default {
    props: {
        currentMonth: {
            type: String,
            required: true
        },
        currentYear: {
            type: Number,
            required: true
        }
    }
}
</script>
