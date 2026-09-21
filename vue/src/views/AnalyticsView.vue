<script setup></script>

<template>
    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow space-y-6">
        <div
            class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
        >
            <div>
                <h1 class="text-xl font-bold text-brand-600 mb-1">
                    linkcut.ru/7aB3dE
                </h1>
                <p class="text-xs text-gray-500 truncate max-w-xl">
                    Цель:
                    https://github.com/laravel/laravel/releases/tag/v11.0.0
                </p>
            </div>
            <!-- QR-код -->
            <div
                class="flex items-center gap-4 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100 dark:border-gray-800"
            >
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=https://linkcut.ru/7aB3dE"
                    alt="QR Code"
                    class="w-16 h-16 rounded border"
                />
                <button
                    class="text-xs bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded hover:bg-gray-200"
                >
                    Скачать QR
                </button>
            </div>
        </div>

        <!-- Графики -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800"
            >
                <h2 class="text-sm font-semibold mb-4">Динамика переходов</h2>
                <div class="h-64"><canvas id="clicksChart"></canvas></div>
            </div>
            <div
                class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800"
            >
                <h2 class="text-sm font-semibold mb-4">Устройства</h2>
                <div class="h-64"><canvas id="devicesChart"></canvas></div>
            </div>
        </div>
    </main>
</template>
