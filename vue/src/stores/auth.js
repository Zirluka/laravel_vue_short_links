import { defineStore } from 'pinia';
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        isInit: false
    }),

    getters: {
        isAuthenticated: (state) => !!state.token && !!state.user
    },

    actions: {
        // Вход по email
        async login(credentials) {
            const resonse = await axios.post('/api/login', credentials)
            const token = resonse.data.token

            this.token = token
            localStorage.setItem('token', token)
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

            await this.fetchUser();
        },

        // Получение пользователя
        async fetchUser() {
            if (!this.token) {
                this.user = null
                this.isInit = true
                return
            }

            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

            try {
                const response = await axios.get('/api/user');
                this.user = response.user
            } catch (error) {
                this.logout()
            } finally {
                this.isInit = true
            }
        },

        // Выход
        async logout() {
            try {
                if (this.token) {
                    await axios.post('/api/logout');
                }
            } catch (error) {
                // Ignore
            } finally {
                this.user = null
                this.token = null
                this.isInit = true
                localStorage.removeItem('token')
                delete axios.defaults.headers.common['Authorization']
            }
        }
    }
});

