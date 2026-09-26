<script setup>
import apiClient from "@/api/client";
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const code = route.params.code;
const loading = ref(true);
const error = ref("");
const isPasswordRequired = ref(false);
const password = ref("");
const isSubmittingPassword = ref(false);

// Запрос на получение оригинального URL
const resolveUrl = async () => {
    loading.value = true;
    error.value = "";

    try {
        const response = await apiClient.get(`/api/${code}`);

        if (response.data?.data) {
            const targetUrl = response.data.data;
            window.location.replace(targetUrl);
        }
    } catch (err) {
        loading.value = false;

        if (
            err.response?.status == 403 &&
            err.response?.data?.message == "Link has password"
        ) {
            isPasswordRequired.value = true;
            return;
        }

        if (err.response?.status == 404) {
            error.value = err.response.data.message;
        } else {
            error.value = "Не удалось выполнить перенаправление";
        }
    }
};

const submitPassword = async () => {
    if (!password.value) return;

    isSubmittingPassword.value = true;
    error.value = "";

    try {
        const response = await apiClient.post(`/api/${code}/guard`, {
            password: password.value,
        });

        const targetUrl = response.data?.data;
        if (targetUrl) {
            window.location.replace(targetUrl);
        }
    } catch (err) {
        error.value = err.response.data.message;
    } finally {
        isSubmittingPassword.value = false;
    }
};

onMounted(() => {
    resolveUrl()
});
</script>

<template>
    <div
        class="min-h-[70vh] flex flex-col items-center justify-center px-4 text-center"
    >
        <!-- Состояние загрузки / перенаправления -->
        <div
            v-if="loading && !isPasswordRequired"
            class="flex flex-col items-center gap-4"
        >
            <div
                class="w-10 h-10 border-4 border-brand-600 border-t-transparent rounded-full animate-spin"
            ></div>
            <p class="text-gray-600 dark:text-gray-300 font-medium">
                Перенаправление...
            </p>
        </div>

        <!-- Форма ввода пароля (если ссылка защищена) -->
        <div
            v-else-if="isPasswordRequired"
            class="w-full max-w-sm p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl"
        >
            <h2 class="text-xl font-bold mb-2 text-gray-800 dark:text-gray-100">
                Ссылка защищена
            </h2>
            <p class="text-sm text-gray-500 mb-6">
                Введите пароль для перехода по этой ссылке
            </p>

            <form @submit.prevent="submitPassword" class="space-y-4">
                <input
                    v-model="password"
                    type="password"
                    required
                    placeholder="Введите пароль..."
                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-600 text-sm dark:text-white"
                />

                <p v-if="error" class="text-xs text-red-500 text-left">
                    {{ error }}
                </p>

                <button
                    type="submit"
                    :disabled="isSubmittingPassword"
                    class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white rounded-lg font-medium text-sm transition shadow"
                >
                    {{ isSubmittingPassword ? "Проверка..." : "Перейти" }}
                </button>
            </form>
        </div>

        <!-- Состояние ошибки -->
        <div v-else class="max-w-md">
            <div class="text-5xl mb-4">⚠️</div>
            <h1
                class="text-2xl font-bold mb-2 text-gray-800 dark:text-gray-100"
            >
                Ошибка перехода
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mb-6 text-sm">
                {{ error }}
            </p>

            <button
                @click="router.push('/')"
                class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm rounded-lg transition"
            >
                На главную
            </button>
        </div>
    </div>
</template>
