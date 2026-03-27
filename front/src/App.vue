<template>
  <component :is="currentLayout">
    <router-view />
  </component>

  <!-- Notifications globales (toasts) -->
  <ToastContainer />
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import LayoutAdmin    from '@/components/layouts/LayoutAdmin.vue'
import LayoutPublic   from '@/components/layouts/LayoutPublic.vue'
import LayoutBlank    from '@/components/layouts/LayoutBlank.vue'
import ToastContainer from '@/components/ToastContainer.vue'

const route = useRoute()

const layouts = {
  admin:  LayoutAdmin,
  public: LayoutPublic,
  blank:  LayoutBlank,
}

const currentLayout = computed(() => layouts[route.meta.layout] ?? LayoutPublic)
</script>
