<template>
  <div class="toast-wrap">
    <transition-group name="toast">
      <div
        v-for="n in notif.notifications"
        :key="n.id"
        class="toast-item"
        :class="`toast-${n.type}`"
      >
        <svg v-if="n.type === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else-if="n.type === 'danger'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <svg v-else-if="n.type === 'warning'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span class="toast-text">{{ n.text }}</span>
        <button class="toast-close" @click="notif.remove(n.id)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useNotifStore } from '@/stores/notif.js'
const notif = useNotifStore()
</script>

<style scoped>
.toast-wrap {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 99999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 360px;
}

.toast-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 600;
  font-family: 'DM Sans', sans-serif;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  min-width: 280px;
}

.toast-success {
  background: #0F2A1E;
  color: #10B981;
  border: 1px solid rgba(5,150,105,0.3);
}
.toast-danger {
  background: #2A0F0F;
  color: #EF4444;
  border: 1px solid rgba(220,38,38,0.3);
}
.toast-warning {
  background: #2A1F0F;
  color: #F59E0B;
  border: 1px solid rgba(217,119,6,0.3);
}
.toast-info {
  background: #0F1A2A;
  color: #60A5FA;
  border: 1px solid rgba(37,99,235,0.3);
}

.toast-text { flex: 1; }

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  color: currentColor;
  opacity: 0.5;
  padding: 2px;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.toast-close:hover { opacity: 1; }

.toast-enter-active,
.toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from   { opacity: 0; transform: translateX(40px); }
.toast-leave-to     { opacity: 0; transform: translateX(40px); }
</style>