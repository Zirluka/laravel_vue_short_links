<script setup>
import apiClient from "@/api/client";
import { useAuthStore } from "@/stores/auth";
import { computed, onMounted, ref } from "vue";

const authStore = useAuthStore();

// Обновление пользователя
const updateForm = ref({
    name: authStore?.user?.data?.name,
    email: authStore?.user?.data?.email,
    password: "",
});
const updateErrors = ref({
    name: "",
    email: "",
    password: "",
});
const isUpdating = ref(false);

const logoutHandler = async () => {
    await authStore.logout();
};
const updateHandler = async () => {
    updateErrors.value = {
        name: "",
        email: "",
        password: "",
    };
    isUpdating.value = true;

    try {
        await authStore.update(updateForm.value);
    } catch (err) {
        updateErrors.value = err.response?.data?.errors;
    } finally {
        isUpdating.value = false;
    }
};

// Токены
const tokens = ref(null);
const filteredTokens = computed(() =>
    tokens?.value?.filter((token) => token.name !== "auth-token"),
);
const isCreatingToken = ref(false);
const isWaitingToken = ref(false);
const isSavingToken = ref(false);
const lastToken = ref("");
const tokenForm = ref({
    name: "",
});
const tokenErrors = ref({
    name: "",
});

const createToken = async () => {
    tokenErrors.value = {
        name: "",
    };
    isWaitingToken.value = true;

    try {
        const response = await apiClient.post("api/tokens", tokenForm.value);
        tokens.value.unshift({
            id: response.data.data.id,
            name: response.data.data.name,
        });
        tokenForm.value = {
            name: "",
        };
        lastToken.value = response.data.token.plainTextToken
        isCreatingToken.value = false;
        isSavingToken.value = true;
    } catch (error) {
        tokenErrors.value.name = error.response?.data?.message;
    } finally {
        isWaitingToken.value = false;
    }
};
const getTokens = async () => {
    try {
        const response = await apiClient.get("/api/tokens");

        tokens.value = response.data?.data;
    } catch (error) {
        //
    }
};
const revokeToken = async (id) => {
    try {
        await apiClient.delete(`api/tokens/${id}`);
        tokens.value = tokens.value.filter((token) => token.id !== id);
    } catch (error) {
        //
    }
};

onMounted(() => {
    getTokens();
});
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
                    <p
                        v-if="updateErrors?.name"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ updateErrors?.name[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Email</label>
                    <input
                        type="email"
                        v-model="updateForm.email"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="updateErrors?.email"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ updateErrors?.email[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">Пароль</label>
                    <input
                        type="password"
                        v-model="updateForm.password"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                    />
                    <p
                        v-if="updateErrors?.password"
                        class="mt-3 text-sm text-red-500 text-center"
                    >
                        {{ updateErrors?.password[0] }}
                    </p>
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
                    @click="isCreatingToken = true"
                >
                    + Сгенерировать токен
                </button>
            </div>
            <div
                v-for="token in filteredTokens"
                :key="token.id"
                class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 flex justify-between items-center text-xs"
            >
                <code>{{ token.name }}</code>
                <button @click="revokeToken(token.id)" class="text-red-600">
                    Отозвать
                </button>
            </div>
        </div>

        <!-- Модальное окно сохранения токена -->
        <div
            v-if="isCreatingToken"
            class="modal fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up"
            >
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Создание токена</h3>
                    <button
                        @click="isCreatingToken = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        &times;
                    </button>
                </div>
                <form class="space-y-4" @submit.prevent="createToken">
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Имя</label
                        >
                        <input
                            type="text"
                            required
                            v-model="tokenForm.name"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="tokenErrors?.name"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ tokenErrors?.name[0] }}
                        </p>
                    </div>
                    <button
                        :disabled="isWaitingToken"
                        type="submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium"
                    >
                        Создать токен
                    </button>
                </form>
            </div>
        </div>

        <!-- Модальное окно добавления токена -->
        <div
            v-if="isSavingToken"
            class="modal fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up"
            >
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Сохранение токена</h3>
                    <button
                        @click="isSavingToken = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        &times;
                    </button>
                </div>
                <p
                    class="mt-3 text-sm text-red-500 text-center"
                >
                    Сохраните токен!
                </p>
                <p
                    v-if="lastToken"
                    class="mt-3 text-sm text-center"
                >
                    Токен: {{ lastToken }}
                </p>
            </div>
        </div>
    </main>
</template>
