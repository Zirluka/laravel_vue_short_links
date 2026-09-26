import { defineStore } from "pinia";
import apiClient from "@/api/client";
import { ref } from "vue";
import router from "@/router";

export const useAuthStore = defineStore("auth", () => {
    const user = ref(null);
    const token = ref(localStorage.getItem("auth_token") || null);
    const isAuth = ref(!!token.value);

    const setAuthData = (data) => {
        user.value = data.user;
        token.value = data.token;
        isAuth.value = true;
        localStorage.setItem("auth_token", data.token);
    };

    const register = async (credentials) => {
        const response = await apiClient.post("/api/register", credentials);

        setAuthData(response.data);
    };

    const login = async (credentials) => {
        const response = await apiClient.post("/api/login", credentials);
        setAuthData(response.data);
    };

    const fetchUser = async () => {
        if (!token.value) return;

        try {
            const response = await apiClient.get("/api/user");
            user.value = response.data;
            isAuth.value = true;
        } catch (error) {
            logout();
        }
    };

    const logout = async () => {
        try {
            if (token.value) {
                await apiClient.post("/api/logout");
            }
        } finally {
            user.value = null;
            token.value = null;
            isAuth.value = false;
            localStorage.removeItem("auth_token");
            router.push({ name: "index" });
        }
    };

    const update = async (credentials) => {
        if (!token.value) return;
        if (credentials.password == "") {
            delete credentials.password
        }

        const response = await apiClient.patch("/api/user", credentials);
        user.value = response.data.data;
    };

    return {
        user,
        token,
        isAuth,
        login,
        register,
        fetchUser,
        logout,
        update,
    };
});
