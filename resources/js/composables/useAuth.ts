import { computed } from 'vue';

/**
 * Authentication composable - web auth is handled by Laravel/Fortify server-side
 */
export function useAuth() {
    return {
        isAuthenticated: computed(() => false),
        user: computed(() => null),
        token: computed(() => null),
        isLoading: computed(() => false),
        error: computed(() => null),
        login: async () => false,
        logout: async () => {},
        checkAuth: async () => false,
    };
}
