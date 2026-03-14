import { route as routeFn } from '../../vendor/tightenco/ziggy';

declare global {
    var route: typeof routeFn;
}

declare module '@/Layouts/AuthenticatedLayout.vue' {
    import { DefineComponent } from 'vue';
    const component: DefineComponent<{}, {}, any>;
    export default component;
}