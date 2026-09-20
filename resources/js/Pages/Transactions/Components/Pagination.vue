<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <div v-if="links && links.length > 3" class="mt-8 flex items-center justify-center gap-1.5">
    <template v-for="(link, index) in links" :key="index">
      <Link v-if="link.url" :href="link.url"
        class="flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-xl transition-all duration-200 shadow-sm"
        :class="[
          link.active
            ? 'bg-indigo-600 text-white shadow-indigo-200 scale-105'
            : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300'
        ]">
        <!-- Previous -->
        <span v-if="link.label.includes('Previous') || link.label.includes('&laquo;')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </span>
        <!-- Next -->
        <span v-else-if="link.label.includes('Next') || link.label.includes('&raquo;')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
          </svg>
        </span>
        <span v-else>{{ link.label }}</span>
      </Link>

      <!-- Disabled Arrow -->
      <span v-else
        class="flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl border border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed">
        <span v-if="link.label.includes('Previous') || link.label.includes('&laquo;')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </span>
        <span v-else-if="link.label.includes('Next') || link.label.includes('&raquo;')">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
          </svg>
        </span>
        <span v-else>{{ link.label }}</span>
      </span>
    </template>
  </div>
</template>