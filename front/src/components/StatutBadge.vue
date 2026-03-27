<template>
  <span class="badge" :class="badgeClass">
    <i :class="icon" class="me-1"></i>{{ statut }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  statut: { type: String, required: true },
})

const config = {
  'Publié':   { cls: 'bg-success',          icon: 'fas fa-globe'      },
  'Validé':   { cls: 'bg-info text-dark',   icon: 'fas fa-check'      },
  'Brouillon':{ cls: 'bg-secondary',        icon: 'fas fa-pencil-alt' },
}

const badgeClass = computed(() => config[props.statut]?.cls || 'bg-secondary')
const icon       = computed(() => config[props.statut]?.icon || 'fas fa-question')
</script>
