<script setup>
import { useTheme } from "@/composables/useTheme";
import { ref, computed } from "vue";
import QRCode from "qrcode";
import apiClient from "@/api/client";
import { useAuthStore } from "@/stores/auth";

// Авторизация и регистрация с модальным окном
const isLogin = ref(false);
const isRegister = ref(false);
const authStore = useAuthStore();

const loginForm = ref({
    email: "",
    password: "",
});
const loginErrors = ref({
    email: "",
    password: "",
});
const isAuthing = ref(false);

const loginHandle = async () => {
    isAuthing.value = true;
    loginErrors.value = {
        email: "",
        password: "",
    };
    try {
        await authStore.login(loginForm.value);
    } catch (err) {
        loginErrors.value = err.response?.data?.errors;
    } finally {
        isAuthing.value = false;
    }
};

// Состояние формы
const originalURL = ref("");
const isLoading = ref(false);
const errorMessage = ref("");

// Состояние результата
const resultData = ref(null);
const qrCodeDataUrl = ref("");
const fullShortUrl = computed(() => {
    if (!resultData.value?.code) return "";
    return `${window.location.origin}/${resultData.value.code}`;
});

// Отправка формы на бек (POST /api/)
const handleShorten = async () => {
    if (!originalURL.value.trim()) return;

    isLoading.value = true;
    errorMessage.value = "";
    resultData.value = null;

    try {
        const response = await apiClient.post("", {
            link: originalURL.value,
        });

        resultData.value = response.data;

        qrCodeDataUrl.value = await QRCode.toDataURL(fullShortUrl.value, {
            width: 160,
            margin: 1,
            color: {
                dark: "#000000",
                light: "#ffffff",
            },
        });
    } catch (err) {
        errorMessage.value =
            err.response?.data?.message ||
            "Не удалось создать ссылку. Проверьте корректность URL.";
    } finally {
        isLoading.value = false;
    }
};

// Тема
const { isDark, toggleTheme } = useTheme();

const copied = ref(false);

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(
            "http://localhost:5173/" + resultData.value.code,
        );
        copied.value = true;

        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        console.log("Не удалось скопировать: ", error);
    }
};
</script>

<template>
    <!-- Навигация -->
    <header
        class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between"
        >
            <div class="flex items-center gap-2">
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
                <span class="font-bold text-lg tracking-tight">LinkCut</span>
            </div>
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
                    @click="isLogin = true"
                    class="text-sm font-medium hover:text-brand-600"
                >
                    Войти
                </button>
                <button
                    @click="isRegister = true"
                    class="text-sm font-medium hover:text-brand-600"
                >
                    Регистрация
                </button>
                <a
                    href="dashboard.html"
                    class="text-sm font-medium bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg transition-colors"
                    >Панель управления</a
                >
            </div>
        </div>
    </header>

    <!-- Главная секция -->
    <main
        class="max-w-4xl mx-auto px-4 py-16 w-full flex-grow flex flex-col justify-center"
    >
        <div class="text-center mb-10">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4">
                Сокращайте ссылки за один клик
            </h1>
            <p
                class="text-gray-500 dark:text-gray-400 text-lg max-w-2xl mx-auto"
            >
                Быстрый, надежный сервис для управления ссылками, отслеживания
                аналитики и защиты данных.
            </p>
        </div>

        <!-- Форма сокращения -->
        <div
            class="bg-white dark:bg-gray-950 p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800"
        >
            <form
                id="shorten-form"
                @submit.prevent="handleShorten"
                class="flex flex-col sm:flex-row gap-3"
            >
                <input
                    v-model="originalURL"
                    type="url"
                    required
                    :disabled="isLoading"
                    placeholder="Вставьте длинный URL-адрес..."
                    class="flex-grow px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm"
                />
                <button
                    :disabled="isLoading"
                    type="submit"
                    class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-lg font-medium text-sm transition-colors whitespace-nowrap"
                >
                    <span v-if="isLoading">Генерация...</span>
                    <span v-else>Сократить</span>
                </button>
            </form>
        </div>

        <p v-if="errorMessage" class="mt-3 text-sm text-red-500 text-center">
            {{ errorMessage }}
        </p>

        <!-- Результат (Скрыт по умолчанию) -->
        <div
            v-if="resultData"
            id="result-card"
            class="slide-up mt-6 bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4"
        >
            <div class="w-full sm:w-auto truncate">
                <span
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1"
                    >Короткая ссылка</span
                >
                <input
                    id="short-link"
                    readonly
                    :value="fullShortUrl"
                    class="bg-transparent text-brand-600 dark:text-brand-500 font-semibold text-lg outline-none w-full border-none p-0"
                />
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button
                    type="button"
                    @click="copyLink"
                    data-copy="short-link"
                    class="relative flex-1 sm:flex-initial bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                    <span>Копировать</span>
                    <span
                        v-if="copied"
                        class="tooltip show absolute -top-8 bg-gray-900 text-white text-xs px-2 py-1 rounded"
                        >Скопировано!</span
                    >
                </button>
            </div>
        </div>

        <div
            v-if="qrCodeDataUrl"
            class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-col items-center"
        >
            <div
                class="p-2.5 bg-white rounded-xl shadow-sm inline-flex flex-col items-center"
            >
                <img :src="qrCodeDataUrl" alt="QR Code" class="w-28 h-28" />
                <span class="text-[10px] text-gray-500 font-medium mt-1"
                    >Сканируй QR</span
                >
            </div>
        </div>

        <p class="text-xs text-center text-gray-400 mt-4">
            Ссылки анонимных пользователей деактивируются через 7 дней.
        </p>
    </main>

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
                <h3 class="text-lg font-bold">Вход в систему</h3>
                <button
                    @click="isRegister = false"
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
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Пароль</label>
                    <input
                        type="password"
                        required
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                </div>
                <button
                    type="submit"
                    class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium"
                >
                    Войти
                </button>
            </form>
        </div>
    </div>

    <footer
        class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-xs text-gray-500"
    >
        &copy; 2026 LinkCut. Все права защищены.
    </footer>
</template>
