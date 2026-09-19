<script setup></script>

<template>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-full flex flex-col">

  <header class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
      <a href="dashboard.html" class="font-bold text-lg">LinkCut</a>
      <button class="theme-toggle p-2 text-gray-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
      </button>
    </div>
  </header>

  <main class="max-w-4xl mx-auto px-4 py-8 w-full flex-grow space-y-6">
    <h1 class="text-2xl font-bold">Настройки профиля</h1>

    <!-- Управление профилем -->
    <div class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-4">
      <h2 class="text-lg font-semibold">Личные данные</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium mb-1">Имя</label>
          <input type="text" value="Роман" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium mb-1">Email</label>
          <input type="email" value="roman@example.com" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
        </div>
      </div>
      <button class="bg-brand-600 text-white text-xs px-4 py-2 rounded-lg">Сохранить</button>
    </div>

    <!-- API Токены -->
    <div class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-4">
      <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">API Токены</h2>
        <button class="text-xs bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded font-medium">+ Сгенерировать токен</button>
      </div>
      <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 flex justify-between items-center text-xs">
        <code>1|sanctum_token_8a7d6f...</code>
        <button class="text-red-600">Отозвать</button>
      </div>
    </div>
  </main>

  <script :src="'@/assets/js/app.js'"></script>
</body>

</template>
