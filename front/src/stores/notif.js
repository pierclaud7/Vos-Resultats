import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotifStore = defineStore('notif', () => {
  const notifications = ref([])
  let nextId = 0

  function add(text, type = 'success', duration = 4000) {
    const id = ++nextId
    notifications.value.push({ id, text, type })
    setTimeout(() => remove(id), duration)
  }

  function remove(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  function success(text) { add(text, 'success') }
  function error(text)   { add(text, 'danger')  }
  function warning(text) { add(text, 'warning') }
  function info(text)    { add(text, 'info')    }

  return { notifications, add, remove, success, error, warning, info }
})
