<script setup>
import { ref, computed } from "vue";
import QRCode from "qrcode";
import apiClient from "@/api/client";

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
        const response = await apiClient.post("/api", {
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

const copied = ref(false);

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(
            "http://172.26.5.21:5173/" + resultData.value.code,
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

    <footer
        class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-xs text-gray-500"
    >
        &copy; 2026 LinkCut. Все права защищены.
    </footer>
</template>
