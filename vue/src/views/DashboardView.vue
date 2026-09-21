<script setup></script>

<template>
    <!-- Основной контент -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"
        >
            <h1 class="text-2xl font-bold">Мои ссылки</h1>
            <button
                data-modal-target="create-modal"
                class="bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
            >
                <span>+ Создать ссылку</span>
            </button>
        </div>

        <!-- Таблица -->
        <div
            class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm"
        >
            <div
                class="p-4 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row gap-3 justify-between"
            >
                <input
                    type="text"
                    placeholder="Поиск по ссылкам..."
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm w-full sm:w-64"
                />
                <select
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                >
                    <option>Сначала новые</option>
                    <option>По популярности</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 text-gray-500 font-medium text-xs uppercase"
                    >
                        <tr>
                            <th class="px-6 py-3">Короткая ссылка</th>
                            <th class="px-6 py-3">Оригинальный URL</th>
                            <th class="px-6 py-3">Клики</th>
                            <th class="px-6 py-3">Статус</th>
                            <th class="px-6 py-3">Дата создания</th>
                            <th class="px-6 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 dark:divide-gray-800"
                    >
                        <tr
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50"
                        >
                            <td class="px-6 py-4 font-semibold text-brand-600">
                                <a href="analytics.html">linkcut.ru/7aB3dE</a>
                            </td>
                            <td
                                class="px-6 py-4 max-w-xs truncate text-gray-500"
                            >
                                https://github.com/laravel/laravel/releases/tag/v11.0.0
                            </td>
                            <td class="px-6 py-4 font-medium">1,240</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                    >Активна</span
                                >
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                14.09.2026
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a
                                    href="analytics.html"
                                    class="text-xs text-gray-600 dark:text-gray-400 hover:underline"
                                    >Аналитика</a
                                >
                                <button
                                    class="text-xs text-red-600 hover:underline"
                                >
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</template>
