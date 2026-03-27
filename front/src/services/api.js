import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true, // Indispensable pour envoyer le cookie de session Symfony
  headers: {
    'Content-Type': 'application/json',
  },
})

// Intercepteur : redirige vers /login si 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
