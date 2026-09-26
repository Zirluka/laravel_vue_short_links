import { ref } from 'vue';

const isDark = ref(false)

export function useTheme() {
    // Инициализация при загрузке
    const initTheme = () => {
        const savedTheme = localStorage.getItem('theme')
        const preferedDark = window.matchMedia('(prefers-color-scheme: dark)').matches

        if (savedTheme == 'dark' || (!savedTheme && preferedDark)) {
            isDark.value = true
            document.documentElement.classList.add('dark')
        } else {
            isDark.value = false
            document.documentElement.classList.remove('dark')
        }
    }

    const toggleTheme = () => {
        isDark.value = !isDark.value
        if (isDark.value) {
            document.documentElement.classList.add('dark')
            localStorage.setItem('theme', 'dark')
        } else {
            document.documentElement.classList.remove('dark')
            localStorage.setItem('theme', 'light')
        }
    }

    return {
        isDark,
        initTheme,
        toggleTheme
    }
}
