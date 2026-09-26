<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import { useRoute } from "vue-router";
import apiClient from "@/api/client";
import QRCode from "qrcode";
import {
    Chart,
    LineController,
    DoughnutController,
    LineElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

// Регистрируем модули Chart.js
Chart.register(
    LineController,
    DoughnutController,
    LineElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler
);

const route = useRoute();
const baseUrl = import.meta.env.VITE_API_URL || window.location.origin + "/";

// Код ссылки из URL (/analytics/:code)
const shortCode = ref(route.params.code || "");
const selectedDays = ref(30);

// Состояния загрузки и данных
const isLoading = ref(true);
const errorMessage = ref("");
const analyticsData = ref(null);

// QR код
const qrDataUrl = ref("");

// Ссылки на DOM-элементы Canvas
const clicksCanvas = ref(null);
const devicesCanvas = ref(null);

// Экземпляры графиков Chart.js
let clicksChartInstance = null;
let devicesChartInstance = null;

// Полный URL короткой ссылки
const fullShortUrl = computedUrl();
function computedUrl() {
    return `${baseUrl}#/${shortCode.value}`;
}

// 1. Генерация QR кода локально
const generateQrCode = async () => {
    try {
        qrDataUrl.value = await QRCode.toDataURL(fullShortUrl, {
            width: 250,
            margin: 1,
            color: {
                dark: "#000000",
                light: "#ffffff",
            },
        });
    } catch (e) {
        console.error("Ошибка при генерации QR кода", e);
    }
};

// 2. Скачивание QR кода
const downloadQr = () => {
    if (!qrDataUrl.value) return;
    const link = document.createElement("a");
    link.href = qrDataUrl.value;
    link.download = `qr-${shortCode.value}.png`;
    link.click();
};

// 3. Запрос данных аналитики с бэкенда
const fetchAnalytics = async () => {
    isLoading.value = true;
    errorMessage.value = "";

    try {
        const response = await apiClient.get(`/api/link/${shortCode.value}/analytics`, {
            params: { days: selectedDays.value },
        });

        analyticsData.value = response.data.data;

        await nextTick();
        renderCharts();
    } catch (err) {
        if (err.response?.status === 403) {
            errorMessage.value = "У вас нет прав для просмотра аналитики этой ссылки.";
        } else if (err.response?.status === 404) {
            errorMessage.value = "Ссылка не найдена.";
        } else {
            errorMessage.value = err.response?.data?.message || "Ошибка при получении аналитики.";
        }
    } finally {
        isLoading.value = false;
    }
};

// 4. Отрисовка графиков Chart.js
const renderCharts = () => {
    if (!analyticsData.value) return;

    const isDarkMode = document.documentElement.classList.contains("dark");
    const textColor = isDarkMode ? "#9CA3AF" : "#4B5563";
    const gridColor = isDarkMode ? "#374151" : "#E5E7EB";

    // Очищаем старые экземпляры перед новой отрисовкой
    if (clicksChartInstance) clicksChartInstance.destroy();
    if (devicesChartInstance) devicesChartInstance.destroy();

    // --- График кликов (Line Chart) ---
    // clicks_over_time приходит как объект { "2026-09-01": 12, "2026-09-02": 25, ... }
    const clicksOverTime = analyticsData.value.clicks_over_time || {};
    const lineLabels = Object.keys(clicksOverTime);
    const lineValues = Object.values(clicksOverTime);

    if (clicksCanvas.value) {
        clicksChartInstance = new Chart(clicksCanvas.value, {
            type: "line",
            data: {
                labels: lineLabels,
                datasets: [
                    {
                        label: "Клики",
                        data: lineValues,
                        borderColor: "#2563EB",
                        backgroundColor: "rgba(37, 99, 235, 0.1)",
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: { ticks: { color: textColor }, grid: { color: gridColor } },
                    y: {
                        beginAtZero: true,
                        ticks: { color: textColor, precision: 0 },
                        grid: { color: gridColor },
                    },
                },
            },
        });
    }

    // --- График устройств (Doughnut Chart) ---
    // devices приходит как массив объектов: [ { device: 'desktop', count: 62 }, ... ]
    const rawDevices = analyticsData.value.devices || [];
    const doughnutLabels = rawDevices.length
        ? rawDevices.map((d) => d.device || d.name || "Другое")
        : ["Нет данных"];
    const doughnutValues = rawDevices.length
        ? rawDevices.map((d) => d.count || d.total || 0)
        : [1];
    const backgroundColors = rawDevices.length
        ? ["#2563EB", "#3B82F6", "#60A5FA", "#93C5FD", "#BFDBFE"]
        : ["#374151"];

    if (devicesCanvas.value) {
        devicesChartInstance = new Chart(devicesCanvas.value, {
            type: "doughnut",
            data: {
                labels: doughnutLabels,
                datasets: [
                    {
                        data: doughnutValues,
                        backgroundColor: backgroundColors,
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: { color: textColor, padding: 15 },
                    },
                },
            },
        });
    }
};

onMounted(() => {
    generateQrCode();
    fetchAnalytics();
});

onUnmounted(() => {
    if (clicksChartInstance) clicksChartInstance.destroy();
    if (devicesChartInstance) devicesChartInstance.destroy();
});
</script>

<template>
    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow space-y-6">
        <!-- Ошибка -->
        <div
            v-if="errorMessage"
            class="p-4 bg-red-100 dark:bg-red-950/40 border border-red-300 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl text-sm"
        >
            {{ errorMessage }}
        </div>

        <!-- Карточка информации о ссылке и QR -->
        <div
            class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
        >
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-brand-600">
                        {{ fullShortUrl }}
                    </h1>
                    <span
                        v-if="analyticsData"
                        class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"
                    >
                        Всего кликов: {{ analyticsData.total_clicks }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Аналитика по короткому коду: <span class="font-mono">{{ shortCode }}</span>
                </p>
            </div>

            <!-- QR-код -->
            <div
                class="flex items-center gap-4 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100 dark:border-gray-800"
            >
                <img
                    v-if="qrDataUrl"
                    :src="qrDataUrl"
                    alt="QR Code"
                    class="w-16 h-16 rounded border border-gray-200 dark:border-gray-800 bg-white p-1"
                />
                <button
                    @click="downloadQr"
                    :disabled="!qrDataUrl"
                    class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition disabled:opacity-50"
                >
                    Скачать QR
                </button>
            </div>
        </div>

        <!-- Переключатель периода -->
        <div class="flex justify-end items-center gap-2">
            <span class="text-xs text-gray-500">Период:</span>
            <select
                v-model="selectedDays"
                @change="fetchAnalytics"
                class="px-3 py-1.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 focus:outline-none"
            >
                <option :value="7">Последние 7 дней</option>
                <option :value="30">Последние 30 дней</option>
                <option :value="90">Последние 90 дней</option>
            </select>
        </div>

        <!-- Индикатор загрузки -->
        <div
            v-if="isLoading"
            class="text-center py-12 text-sm text-gray-400 animate-pulse"
        >
            Загрузка аналитики...
        </div>

        <!-- Графики -->
        <div v-show="!isLoading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Динамика переходов -->
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800"
            >
                <h2 class="text-sm font-semibold mb-4 text-gray-900 dark:text-white">Динамика переходов</h2>
                <div class="relative h-64 w-full">
                    <canvas ref="clicksCanvas"></canvas>
                </div>
            </div>

            <!-- Устройства -->
            <div
                class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800"
            >
                <h2 class="text-sm font-semibold mb-4 text-gray-900 dark:text-white">Устройства</h2>
                <div class="relative h-64 w-full">
                    <canvas ref="devicesCanvas"></canvas>
                </div>
            </div>
        </div>
    </main>
</template>
