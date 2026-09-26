<script setup>
import { onBeforeMount, ref } from "vue";
import { RouterLink, RouterView } from "vue-router";
import { useTheme } from "./composables/useTheme";
import { useAuthStore } from "./stores/auth";

// Stores init
const authStore = useAuthStore();

// Авторизация и регистрация с модальным окном
const isLogin = ref(false);
const isRegister = ref(false);

const loginForm = ref({
    email: "",
    password: "",
});
const loginErrors = ref({
    email: "",
    password: "",
});
const isAuthing = ref(false);

const registerForm = ref({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});
const registerErrors = ref({
    name: "",
    email: "",
    password: "",
});

const loginHandle = async () => {
    isAuthing.value = true;
    loginErrors.value = {
        email: "",
        password: "",
    };
    try {
        await authStore.login(loginForm.value);
        isLogin.value = false;
    } catch (err) {
        loginErrors.value = err.response?.data?.errors;
    } finally {
        isAuthing.value = false;
    }
};

const registerHandle = async () => {
    isAuthing.value = true;
    registerErrors.value = {
        name: "",
        email: "",
        password: "",
    };
    try {
        await authStore.register(registerForm.value);
        isRegister.value = false;
    } catch (err) {
        registerErrors.value = err.response?.data?.errors;
    } finally {
        isAuthing.value = false;
    }
};



// Тема
const { initTheme, isDark, toggleTheme } = useTheme();

onBeforeMount(() => {
    initTheme();
    authStore.fetchUser();
});
</script>

<template>
    <!-- Навигация -->
    <header
        class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
            <RouterLink :to="{ name: 'index' }" class="flex items-center gap-2">
                <svg
                    class="w-6 h-6 text-brand-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                    />
                </svg>
                <span class="font-bold text-lg tracking-tight">ZipLink</span>
            </RouterLink>
            <div class="flex items-center gap-4">
                <button
                    @click="toggleTheme"
                    class="theme-toggle p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <svg
                        v-if="isDark"
                        class="w-5 h-5 hidden dark:block"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"
                        />
                    </svg>
                    <svg
                        v-else
                        class="w-5 h-5 dark:hidden"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        />
                    </svg>
                </button>
                <button
                    v-if="!authStore.isAuth"
                    @click="isLogin = true"
                    class="text-sm font-medium hover:text-brand-600"
                >
                    Войти
                </button>
                <button
                    v-if="!authStore.isAuth"
                    @click="isRegister = true"
                    class="text-sm font-medium hover:text-brand-600"
                >
                    Регистрация
                </button>
                <RouterLink
                    v-if="authStore.isAuth"
                    :to="{ name : 'settings' }"
                    class="text-sm font-medium px-4 py-2 rounded-lg transition-colors"
                    >{{ authStore?.user?.data?.name }}</RouterLink
                >
                <RouterLink
                    :to="{ name : 'dashboard' }"
                    class="text-sm font-medium bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg transition-colors"
                    >Панель управления</RouterLink
                >
            </div>
        </div>
    </header>

    <RouterView />

    <!-- Модальное окно авторизации -->
    <div
        v-if="isLogin"
        class="modal fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    >
        <div
            class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up"
        >
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold">Вход в систему</h3>
                <button
                    @click="isLogin = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    &times;
                </button>
            </div>
            <form class="space-y-4" @submit.prevent="loginHandle">
                <div>
                    <label class="block text-xs font-medium mb-1">Email</label>
                    <input
                        type="email"
                        required
                        v-model="loginForm.email"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="loginErrors.email"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ loginErrors.email[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Пароль</label>
                    <input
                        type="password"
                        required
                        v-model="loginForm.password"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="loginErrors.password"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ loginErrors.password[0] }}
                    </p>
                </div>
                <button
                    :disabled="isAuthing"
                    type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium"
                >
                    Войти
                </button>
            </form>
        </div>
    </div>

    <!-- Модальное окно регистрации -->
    <div
        v-if="isRegister"
        class="modal fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    >
        <div
            class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up"
        >
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold">Регистрация в системе</h3>
                <button
                    @click="isRegister = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    &times;
                </button>
            </div>
            <form class="space-y-4" @submit.prevent="registerHandle">
                <div>
                    <label class="block text-xs font-medium mb-1">Имя</label>
                    <input
                        type="text"
                        required
                        v-model="registerForm.name"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="registerErrors?.name"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ registerErrors?.name[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Email</label>
                    <input
                        type="email"
                        required
                        v-model="registerForm.email"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="registerErrors?.email"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ registerErrors?.email[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Пароль</label>
                    <input
                        type="password"
                        required
                        v-model="registerForm.password"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="registerErrors?.password"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ registerErrors?.password[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1"
                        >Повторите пароль</label
                    >
                    <input
                        type="password"
                        required
                        v-model="registerForm.password_confirmation"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="registerErrors?.password"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ registerErrors?.password[0] }}
                    </p>
                </div>
                <button
                    :disabled="isAuthing"
                    type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium"
                >
                    Зарегистрироваться
                </button>
            </form>
        </div>
    </div>
</template>
