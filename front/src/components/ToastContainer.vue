<template>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <transition-group name="toast">
      <div
        v-for="n in notif.notifications"
        :key="n.id"
        class="toast show align-items-center border-0 shadow mb-2"
        :class="`text-bg-${n.type}`"
        role="alert"
      >
        <div class="d-flex">
          <div class="toast-body fw-semibold">
            <i :class="icon(n.type)" class="me-2"></i>{{ n.text }}
          </div>
          <button class="btn-close btn-close-white me-2 m-auto" @click="notif.remove(n.id)"></button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useNotifStore } from '@/stores/notif.js'

const notif = useNotifStore()

function icon(type) {
  return {
    success: 'fas fa-check-circle',
    danger:  'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info:    'fas fa-info-circle',
  }[type] || 'fas fa-bell'
}
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateX(50px); }
.toast-leave-to   { opacity: 0; transform: translateX(50px); }
</style>
