<template>
  <span class="badge" :class="badgeClass">
    <i :class="icon" class="me-1"></i>{{ resultat }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  resultat: { type: String, required: true },
})

const config = {
  'Admis':     { cls: 'bg-success',          icon: 'fas fa-check-circle'  },
  'Rattrapage':{ cls: 'bg-warning text-dark', icon: 'fas fa-redo'          },
  'Refusé':    { cls: 'bg-danger',            icon: 'fas fa-times-circle'  },
  'En attente':{ cls: 'bg-secondary',         icon: 'fas fa-clock'         },
}

const badgeClass = computed(() => config[props.resultat]?.cls || 'bg-secondary')
const icon       = computed(() => config[props.resultat]?.icon || 'fas fa-question')
</script>
