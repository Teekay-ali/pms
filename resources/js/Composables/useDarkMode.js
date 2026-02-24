import { ref, watch, onMounted } from 'vue'

const isDark = ref(false)

export function useDarkMode() {
    const init = () => {
        const stored = localStorage.getItem('sitepro-theme')
        if (stored) {
            isDark.value = stored === 'dark'
        } else {
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
        }
        applyTheme()
    }

    const applyTheme = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    }

    const toggle = () => {
        isDark.value = !isDark.value
        localStorage.setItem('sitepro-theme', isDark.value ? 'dark' : 'light')
        applyTheme()
    }

    onMounted(() => init())

    watch(isDark, applyTheme)

    return { isDark, toggle }
}
