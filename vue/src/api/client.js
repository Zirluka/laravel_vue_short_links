import axios from 'axios'

const apiClient = axios.create({
    baseURL: 'http://localhost',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
})

apiClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

export default apiClient
