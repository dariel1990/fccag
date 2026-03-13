import { router } from '@inertiajs/vue3';

/**
 * Data source composable - wraps Inertia router methods
 */
export function useDataSource() {
    /**
     * GET request
     */
    const get = async <T = any>(
        url: string,
        options?: { query?: Record<string, any> },
    ): Promise<T> => {
        return new Promise((resolve, reject) => {
            router.get(url, options?.query || {}, {
                preserveState: true,
                onSuccess: (page) => resolve(page.props as T),
                onError: (errors) => reject(errors),
            });
        });
    };

    /**
     * POST request
     */
    const post = async <T = any>(
        url: string,
        data?: Record<string, any>,
    ): Promise<T> => {
        return new Promise((resolve, reject) => {
            router.post(url, data || {}, {
                onSuccess: (page) => resolve(page.props as T),
                onError: (err) => reject(err),
            });
        });
    };

    /**
     * PUT request
     */
    const put = async <T = any>(
        url: string,
        data?: Record<string, any>,
    ): Promise<T> => {
        return new Promise((resolve, reject) => {
            router.put(url, data || {}, {
                onSuccess: (page) => resolve(page.props as T),
                onError: (err) => reject(err),
            });
        });
    };

    /**
     * DELETE request
     */
    const destroy = async <T = any>(url: string): Promise<T> => {
        return new Promise((resolve, reject) => {
            router.delete(url, {
                onSuccess: (page) => resolve(page.props as T),
                onError: (err) => reject(err),
            });
        });
    };

    /**
     * Navigate to a route
     */
    const navigate = (url: string, options?: { replace?: boolean }): void => {
        router.visit(url, { replace: options?.replace });
    };

    return {
        get,
        post,
        put,
        delete: destroy,
        navigate,
    };
}
