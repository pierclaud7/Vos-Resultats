<template>
  <transition name="fade">
    <div
      v-if="text"
      class="alert d-flex align-items-center gap-2 shadow-sm"
      :class="`alert-${type}`"
      role="alert"
    >
      <i :class="icon"></i>
      <span>{{ text }}</span>
      <button class="btn-close ms-auto" @click="$emit('close')"></button>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: { type: String, default: '' },
  type: { type: String, default: 'success' }, // success | danger | warning | info
})

defineEmits(['close'])

const icon = computed(() => ({
  success: 'fas fa-check-circle',
  danger:  'fas fa-exclamation-circle',
  warning: 'fas fa-exclamation-triangle',
  info:    'fas fa-info-circle',
}[props.type] || 'fas fa-info-circle'))
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
