<template>
  <div v-if="show" class="modal d-block" style="background:rgba(0,0,0,.5);" @click.self="$emit('cancel')">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
            Confirmation
          </h5>
        </div>
        <div class="modal-body py-2">
          <p class="mb-0">{{ message }}</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button class="btn btn-secondary btn-sm" @click="$emit('cancel')">
            Annuler
          </button>
          <button class="btn btn-danger btn-sm" @click="$emit('confirm')">
            <i class="fas fa-trash me-1"></i> Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show:    { type: Boolean, default: false },
  message: { type: String,  default: 'Êtes-vous sûr de vouloir supprimer cet élément ?' },
})
defineEmits(['confirm', 'cancel'])
</script>
