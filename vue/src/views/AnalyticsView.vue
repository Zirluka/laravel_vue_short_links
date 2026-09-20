<script setup></script>

<template>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-full flex flex-col">

  <header class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
      <a href="dashboard.html" class="text-sm font-medium text-brand-600 flex items-center gap-1">&larr; Назад в панель</a>
      <button class="theme-toggle p-2 rounded-lg text-gray-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
      </button>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow space-y-6">
    <div class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-xl font-bold text-brand-600 mb-1">linkcut.ru/7aB3dE</h1>
        <p class="text-xs text-gray-500 truncate max-w-xl">Цель: https://github.com/laravel/laravel/releases/tag/v11.0.0</p>
      </div>
      <!-- QR-код -->
      <div class="flex items-center gap-4 border-t md:border-t-0 pt-4 md:pt-0 border-gray-100 dark:border-gray-800">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=https://linkcut.ru/7aB3dE" alt="QR Code" class="w-16 h-16 rounded border">
        <button class="text-xs bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded hover:bg-gray-200">Скачать QR</button>
      </div>
    </div>

    <!-- Графики -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800">
        <h2 class="text-sm font-semibold mb-4">Динамика переходов</h2>
        <div class="h-64"><canvas id="clicksChart"></canvas></div>
      </div>
      <div class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800">
        <h2 class="text-sm font-semibold mb-4">Устройства</h2>
        <div class="h-64"><canvas id="devicesChart"></canvas></div>
      </div>
    </div>
  </main>
</body>
</template>
