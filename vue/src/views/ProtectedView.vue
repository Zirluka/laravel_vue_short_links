<script setup></script>

<template>
        <body
            class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 h-full flex items-center justify-center p-4"
        >
            <div
                class="bg-white dark:bg-gray-950 p-6 sm:p-8 rounded-xl border border-gray-200 dark:border-gray-800 max-w-md w-full shadow-lg text-center slide-up"
            >
                <div
                    class="w-12 h-12 bg-gray-100 dark:bg-gray-800 text-brand-600 rounded-full flex items-center justify-center mx-auto mb-4"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                </div>
                <h1 class="text-xl font-bold mb-2">Ссылка защищена паролем</h1>
                <p class="text-xs text-gray-500 mb-6">
                    Введите пароль, указанный автором ссылки, чтобы продолжить
                    переход.
                </p>

                <form
                    class="space-y-4"
                    onsubmit="
                        event.preventDefault();
                        alert('Пароль принят, перенаправление...');
                    "
                >
                    <input
                        type="password"
                        required
                        placeholder="Введите пароль"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-brand-600"
                    />
                    <button
                        type="submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors"
                    >
                        Перейти по ссылке
                    </button>
                </form>
            </div>

            <script :src="'@/assets/js/app.js'"></script>
        </body>
</template>
