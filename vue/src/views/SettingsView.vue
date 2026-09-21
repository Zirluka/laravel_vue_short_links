<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";

const authStore = useAuthStore();

const updateForm = ref({
    name: authStore?.user?.data?.name,
    email: authStore?.user?.data?.email,
});
const updateErrors = ref({
    name: "",
    email: "",
});
const isUpdating = ref(false);

const logoutHandler = async () => {
    await authStore.logout();
};
const updateHandler = async () => {
    updateErrors.value = {
        name: "",
        email: "",
    };
    isUpdating.value = true;

    try {
        await authStore.update(updateForm.value);
    } catch (err) {
        updateErrors.value = err.response?.data?.errors;
    } finally {
        isUpdating.value = false
    }
};
</script>

<template>
    <main class="max-w-4xl mx-auto px-4 py-8 w-full flex-grow space-y-6">
        <h1 class="text-2xl font-bold">Настройки профиля</h1>

        <!-- Управление профилем -->
        <form
            @submit.prevent="updateHandler"
            class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-4"
        >
            <h2 class="text-lg font-semibold">Личные данные</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1">Имя</label>
                    <input
                        type="text"
                        v-model="updateForm.name"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Email</label>
                    <input
                        type="email"
                        v-model="updateForm.email"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                </div>
            </div>
            <button
                type="submit"
                class="bg-brand-600 text-white text-xs px-4 py-2 rounded-lg"
            >
                Сохранить
            </button>
            <button
                @click="logoutHandler"
                type="button"
                class="bg-red-500 ml-2 text-white text-xs px-4 py-2 rounded-lg"
            >
                Выйти
            </button>
        </form>

        <!-- API Токены -->
        <div
            class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-4"
        >
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">API Токены</h2>
                <button
                    class="text-xs bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded font-medium"
                >
                    + Сгенерировать токен
                </button>
            </div>
            <div
                class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 flex justify-between items-center text-xs"
            >
                <code>1|sanctum_token_8a7d6f...</code>
                <button class="text-red-600">Отозвать</button>
            </div>
        </div>
    </main>
</template>
