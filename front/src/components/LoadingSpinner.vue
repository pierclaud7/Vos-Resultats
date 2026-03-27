<template>
  <div class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Chargement...</span>
    </div>
    <p v-if="label" class="text-muted mt-3 small">{{ label }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, default: '' },
})
</script>
