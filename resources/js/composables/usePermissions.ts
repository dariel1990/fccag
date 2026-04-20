import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();
    const auth = page.props.auth as {
        isSuperAdmin: boolean;
        permissions: Record<string, string[] | true>;
    };

    const can = (module: string, action: string): boolean => {
        if (auth.isSuperAdmin) {
            return true;
        }

        const perms = auth.permissions ?? {};

        if (perms['*'] === true) {
            return true;
        }

        const modulePerms = perms[module];

        return Array.isArray(modulePerms) && modulePerms.includes(action);
    };

    const canAny = (module: string): boolean => can(module, 'read');

    return {
        can,
        canAny,
        isSuperAdmin: auth.isSuperAdmin,
    };
}
