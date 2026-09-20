import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { PageProps } from '@/types';

export function useAutoDismissFlash(timeout = 4000) {
    const page = usePage<PageProps>();

    watch(
        () => [page.props.flash?.success, page.props.flash?.error],
        ([success, error]) => {
            if (success || error) {
                const timer = setTimeout(() => {
                    if (page.props.flash) {
                        page.props.flash.success = null;
                        page.props.flash.error = null;
                    }
                }, timeout);

                return () => clearTimeout(timer);
            }
        },
        { immediate: true }
    );
}