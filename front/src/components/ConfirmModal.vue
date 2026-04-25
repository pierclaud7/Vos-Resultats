<template>
  <Teleport to="body">
    <div v-if="show" class="cm-overlay" @click.self="$emit('cancel')">
      <div class="cm-box">
        <div class="cm-head">
          <div class="cm-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <h3 class="cm-title">Confirmation</h3>
        </div>
        <div class="cm-body">
          <p class="cm-message">{{ message }}</p>
        </div>
        <div class="cm-foot">
          <button class="cm-btn-cancel" @click="$emit('cancel')">Annuler</button>
          <button class="cm-btn-confirm" @click="$emit('confirm')">Supprimer</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  show:    { type: Boolean, default: false },
  message: { type: String,  default: 'Êtes-vous sûr de vouloir supprimer cet élément ?' },
})
defineEmits(['confirm', 'cancel'])
</script>

<style>
.cm-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.cm-box {
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.5);
  position: relative;
  z-index: 10000;
}

.cm-head {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.cm-icon {
  width: 36px;
  height: 36px;
  background: rgba(217,119,6,0.12);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #F59E0B;
  flex-shrink: 0;
}

.cm-title {
  font-size: 16px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.cm-body {
  padding: 20px 24px;
}

.cm-message {
  font-size: 14px;
  color: #64748B;
  margin: 0;
  line-height: 1.6;
}

.cm-foot {
  padding: 16px 24px;
  border-top: 1px solid rgba(255,255,255,0.06);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.cm-btn-cancel {
  padding: 9px 18px;
  background: rgba(255,255,255,0.04);
  color: #64748B;
  border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: all 0.15s;
}
.cm-btn-cancel:hover { background: rgba(255,255,255,0.07); color: #94A3B8; }

.cm-btn-confirm {
  padding: 9px 18px;
  background: #DC2626;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: background 0.15s;
}
.cm-btn-confirm:hover { background: #B91C1C; }
</style>