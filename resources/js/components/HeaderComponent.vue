<template>
  <header class="bg-white shadow-md sticky top-0 z-10 transition-all duration-300" :class="{ 'py-2': scrolled, 'py-4': !scrolled }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-primary-color" data-aos="fade-right">
          Financial Planner
        </h1>
        <nav>
          <ul class="flex space-x-6">
            <li v-for="(item, index) in navItems" :key="index">
              <a :href="item.href" class="text-gray-600 hover:text-primary-color transition-colors duration-300" data-aos="fade-down" :data-aos-delay="index * 100">
                {{ item.text }}
              </a>
            </li>
          </ul>
        </nav>
        <div class="flex items-center space-x-4">
          <div class="text-gray-500 flex items-center" data-aos="fade-left">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span class="animate__animated animate__fadeIn">{{ currentMonth }} {{ currentYear }}</span>
          </div>
          <div v-if="auth && auth.user" class="flex items-center space-x-2">
            <span class="text-gray-600">{{ auth.user.name }}</span>
            <button @click="logout" class="text-gray-600 hover:text-primary-color transition-colors duration-300">
              Logout
            </button>
          </div>
          <div v-else class="flex items-center space-x-2">
            <a href="/login" class="text-gray-600 hover:text-primary-color transition-colors duration-300">Login</a>
            <a href="/register" class="text-gray-600 hover:text-primary-color transition-colors duration-300">Register</a>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  props: {
    currentMonth: {
      type: String,
      required: true
    },
    currentYear: {
      type: Number,
      required: true
    },
    auth: Object
  },
  data() {
    return {
      scrolled: false,
      navItems: [
        { text: 'Dashboard', href: '/' },
        { text: 'Upload Statement', href: '/upload-bank-statement' },
      ]
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    console.log('HeaderComponent mounted, auth:', this.auth);
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  watch: {
    auth: {
      handler(newAuth) {
        console.log('Auth object changed:', newAuth);
      },
      deep: true
    }
  },
  methods: {
    handleScroll() {
      this.scrolled = window.scrollY > 0;
    },
    logout() {
      const form = useForm({});
      form.post('/logout', {
        preserveState: false,
        preserveScroll: false,
      });
    }
  }
}
</script>

<style scoped>
.text-primary-color {
  color: var(--primary-color);
}
</style>
