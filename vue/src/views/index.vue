<script setup></script>

<template>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 h-full flex flex-col justify-between">

  <!-- Навигация -->
  <header class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        <span class="font-bold text-lg tracking-tight">LinkCut</span>
      </div>
      <div class="flex items-center gap-4">
        <button class="theme-toggle p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
          <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
          <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/></svg>
        </button>
        <button data-modal-target="auth-modal" class="text-sm font-medium hover:text-brand-600">Войти</button>
        <a href="dashboard.html" class="text-sm font-medium bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg transition-colors">Панель управления</a>
      </div>
    </div>
  </header>

  <!-- Главная секция -->
  <main class="max-w-4xl mx-auto px-4 py-16 w-full flex-grow flex flex-col justify-center">
    <div class="text-center mb-10">
      <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4">Сокращайте ссылки за один клик</h1>
      <p class="text-gray-500 dark:text-gray-400 text-lg max-w-2xl mx-auto">Быстрый, надежный сервис для управления ссылками, отслеживания аналитики и защиты данных.</p>
    </div>

    <!-- Форма сокращения -->
    <div class="bg-white dark:bg-gray-950 p-3 sm:p-4 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800">
      <form id="shorten-form" onsubmit="event.preventDefault(); document.getElementById('result-card').classList.remove('hidden');" class="flex flex-col sm:flex-row gap-3">
        <input type="url" required placeholder="Вставьте длинный URL-адрес..." class="flex-grow px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 text-sm">
        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-lg font-medium text-sm transition-colors whitespace-nowrap">Сократить</button>
      </form>
    </div>

    <!-- Результат (Скрыт по умолчанию) -->
    <div id="result-card" class="hidden slide-up mt-6 bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="w-full sm:w-auto truncate">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Короткая ссылка</span>
        <input id="short-link" readonly value="https://linkcut.ru/7aB3dE" class="bg-transparent text-brand-600 dark:text-brand-500 font-semibold text-lg outline-none w-full border-none p-0">
      </div>
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <button data-copy="short-link" class="relative flex-1 sm:flex-initial bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2">
          <span>Копировать</span>
          <span class="tooltip absolute -top-8 bg-gray-900 text-white text-xs px-2 py-1 rounded">Скопировано!</span>
        </button>
      </div>
    </div>

    <p class="text-xs text-center text-gray-400 mt-4">Ссылки анонимных пользователей деактивируются через 7 дней.</p>
  </main>

  <!-- Модальное окно авторизации -->
  <div id="auth-modal" class="modal hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold">Вход в систему</h3>
        <button data-modal-close class="text-gray-400 hover:text-gray-600">&times;</button>
      </div>
      <form class="space-y-4" onsubmit="event.preventDefault(); window.location.href='dashboard.html';">
        <div>
          <label class="block text-xs font-medium mb-1">Email</label>
          <input type="email" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium mb-1">Пароль</label>
          <input type="password" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm">
        </div>
        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium">Войти</button>
      </form>
    </div>
  </div>

  <footer class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-xs text-gray-500">
    &copy; 2026 LinkCut. Все права защищены.
  </footer>

  <script :src="'@/assets/js/app.js'"></script>
</body>
</template>
