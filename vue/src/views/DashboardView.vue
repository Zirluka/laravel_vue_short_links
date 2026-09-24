<script setup>
import apiClient from "@/api/client";
import { computed, onMounted, ref } from "vue";

const baseUrl = import.meta.env.VITE_API_URL;

// sort
const sortBy = ref("newest");
const search = ref("");
const sortedUserLinks = computed(() => {
    if (!userLinks.value || !Array.isArray(userLinks.value)) {
        return [];
    }

    let filteredUsers = userLinks.value;

    if (search.value && search.value.trim() !== "") {
        const query = search.value.toLowerCase().trim();
        filteredUsers = filteredUsers.filter((sublink) =>
            sublink.original_url
                ? sublink.original_url.toLowerCase().includes(query)
                : false,
        );
    }

    if (sortBy.value === "newest") {
        return [...filteredUsers].sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });
    } else {
        return [...filteredUsers].sort((a, b) => {
            return (b.clicks_count || 0) - (a.clicks_count || 0);
        });
    }
});

// Link create
const isCreatingLink = ref(false);
const isCreate = ref(false);
const createLinkForm = ref({
    link: "",
    password: "",
    expired_at: "",
});
const createLinkErrors = ref({
    link: "",
    password: "",
    expired_at: "",
});

const createLinkHandle = async () => {
    isCreate.value = true;
    createLinkErrors.value = {
        link: "",
        password: "",
        expired_at: "",
    };

    try {
        const response = await apiClient.post("/api", createLinkForm.value);
        userLinks.value.push(response.data.link)
        isCreatingLink.value = false
    } catch (err) {
        // console.log(err);

        createLinkErrors.value =
            err.response?.data?.message ||
            "Не удалось создать ссылку. Проверьте корректность URL.";
    } finally {
        isCreate.value = false;
    }
};

// link edit
const isEditingLink = ref(false)
const editNow = ref(0)

const editLink = (id) => {
    editNow.value = id
    isEditingLink.value = true
}

// link get
const userLinks = ref(null);

const getUserUrls = async () => {
    try {
        const response = await apiClient.get("/api/link");
        userLinks.value = response.data.data;
    } catch (error) {
        //
    }
};

onMounted(() => {
    getUserUrls();
});
</script>

<template>
    <!-- Основной контент -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"
        >
            <h1 class="text-2xl font-bold">Мои ссылки</h1>
            <button
                @click="isCreatingLink = true"
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
                    v-model="search"
                    type="text"
                    placeholder="Поиск по ссылкам..."
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm w-full sm:w-64"
                />
                <select
                    v-model="sortBy"
                    class="px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                >
                    <option value="newest">Сначала новые</option>
                    <option value="popular">По популярности</option>
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
                            <th class="px-6 py-3">Дата истечения</th>
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
                            v-for="link in sortedUserLinks"
                            :key="link.id"
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50"
                        >
                            <td class="px-6 py-4 font-semibold text-brand-600">
                                <a :href="baseUrl + link['short_code']"
                                    >{{ baseUrl }}{{ link["short_code"] }}</a
                                >
                            </td>
                            <td
                                class="px-6 py-4 max-w-xs truncate text-gray-500"
                            >
                                {{ link["original_url"] }}
                            </td>
                            <td class="px-6 py-4 font-medium">
                                {{
                                    new Intl.DateTimeFormat("ru-RU").format(
                                        new Date(link["expired_at"]),
                                    )
                                }}
                            </td>
                            <td class="px-6 py-4 font-medium">
                                {{ link["clicks_count"] }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="link.is_active"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                                    >Активна</span
                                >
                                <span
                                    v-if="!link.is_active"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400"
                                    >Неактивна</span
                                >
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{
                                    new Intl.DateTimeFormat("ru-RU").format(
                                        new Date(link["created_at"]),
                                    )
                                }}
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <router-link
                                    :to="{
                                        name: 'analytics',
                                        params: { id: link['id'] },
                                    }"
                                    class="text-xs text-gray-600 dark:text-gray-400 hover:underline"
                                    >Аналитика
                                </router-link>
                                <button
                                    @click="editLink(link['id'])"
                                    class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                >
                                    Изменить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Модальное окно создания ссылки -->
        <div
            v-if="isCreatingLink"
            class="modal fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up"
            >
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Создание короткой ссылки</h3>
                    <button
                        @click="isCreatingLink = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        &times;
                    </button>
                </div>
                <form class="space-y-4" @submit.prevent="createLinkHandle">
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Оригинальный URL</label
                        >
                        <input
                            type="text"
                            required
                            v-model="createLinkForm.link"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="createLinkErrors?.link"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ createLinkErrors?.link[0] }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Пароль (оставте пустым, если пароль не
                            нужен)</label
                        >
                        <input
                            type="password"
                            v-model="createLinkForm.password"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="createLinkErrors?.password"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ createLinkErrors?.password[0] }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Дата истечения</label
                        >
                        <input
                            type="date"
                            v-model="createLinkForm.expired_at"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="createLinkErrors?.expired_at"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ createLinkErrors?.expired_at[0] }}
                        </p>
                    </div>
                    <button
                        :disabled="isCreate"
                        type="submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium"
                    >
                        Создать
                    </button>
                </form>
            </div>
        </div>

        <!-- Модальное окно изменения ссылки -->
        <div
            v-if="isEditingLink"
            class="modal fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
        >
            <div
                class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl p-6 max-w-md w-full shadow-2xl slide-up"
            >
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Создание короткой ссылки</h3>
                    <button
                        @click="isEditingLink = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        &times;
                    </button>
                </div>
                <form class="space-y-4" @submit.prevent="createLinkHandle">
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Оригинальный URL</label
                        >
                        <input
                            type="text"
                            required
                            v-model="createLinkForm.link"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="createLinkErrors?.link"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ createLinkErrors?.link[0] }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Пароль (оставте пустым, если пароль не
                            нужен)</label
                        >
                        <input
                            type="password"
                            v-model="createLinkForm.password"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="createLinkErrors?.password"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ createLinkErrors?.password[0] }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1"
                            >Дата истечения</label
                        >
                        <input
                            type="date"
                            v-model="createLinkForm.expired_at"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-sm"
                        />
                        <p
                            v-if="createLinkErrors?.expired_at"
                            class="mt-3 text-sm text-red-500 text-center"
                        >
                            {{ createLinkErrors?.expired_at[0] }}
                        </p>
                    </div>
                    <button
                        :disabled="isCreate"
                        type="submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-lg text-sm font-medium"
                    >
                        Создать
                    </button>
                    <button
                        :disabled="isCreate"
                        type="button"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-medium"
                    >
                        Деактивировать
                    </button>
                    <button
                        :disabled="isCreate"
                        type="button"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-medium"
                    >
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </main>
</template>
