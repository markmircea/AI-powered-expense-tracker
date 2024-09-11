<template>
  <header class="sticky top-0 z-50 transition-all duration-300" :class="{ 'py-2': scrolled, 'py-4': !scrolled }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="glass rounded-lg p-4">
        <div class="flex justify-between items-center">
          <h1 class="text-3xl font-bold gradient-text animate-pulse" data-aos="fade-right">
            Expense Tracker
          </h1>
          <nav class="hidden md:block">
            <ul class="flex space-x-6">
              <li v-for="(item, index) in navItems" :key="index">
                <a :href="item.href" class="text-gray-600 hover:text-primary-color transition-colors duration-300" data-aos="fade-down" :data-aos-delay="index * 100">
                  {{ item.text }}
                </a>
              </li>
            </ul>
          </nav>
          <div class="flex items-center space-x-4">
            <div class="text-gray-500 flex items-center animate-fadeIn" data-aos="fade-left">
              <i class="fas fa-calendar-alt mr-2"></i>
              <span>{{ currentMonth }} {{ currentYear }}</span>
            </div>
            <div v-if="auth && auth.user" class="flex items-center space-x-2">
              <span class="text-gray-600">{{ auth.user.name }}</span>
              <button @click="logout" class="btn btn-primary text-sm">
                Logout
              </button>
            </div>
            <div v-else class="flex items-center space-x-2">
              <a href="/login" class="btn btn-primary text-sm">Login</a>
              <a href="/register" class="btn btn-secondary text-sm">Register</a>
            </div>
          </div>
        </div>
        <!-- Mobile menu button -->
        <div class="md:hidden mt-4">
          <button @click="toggleMobileMenu" class="text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    <!-- Mobile menu -->
    <div v-if="mobileMenuOpen" class="md:hidden glass mt-2 rounded-lg p-4 animate-slideIn">
      <nav>
        <ul class="space-y-2">
          <li v-for="(item, index) in navItems" :key="index">
            <a :href="item.href" class="block text-gray-600 hover:text-primary-color transition-colors duration-300" @click="mobileMenuOpen = false">
              {{ item.text }}
            </a>
          </li>
        </ul>
      </nav>
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
      mobileMenuOpen: false,
      navItems: [
        { text: 'Dashboard', href: '/' },
        { text: 'Upload Statement', href: '/upload-bank-statement' },
        { text: 'Manage Teams', href: '/teams'}
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
    },
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen;
    }
  }
}
</script>

<style scoped>
.text-primary-color {
  color: var(--primary-color);
}
</style>
