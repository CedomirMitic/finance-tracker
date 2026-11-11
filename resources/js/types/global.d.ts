import { route as routeFn } from '../../vendor/tightenco/ziggy';

declare global {
    var route: typeof routeFn;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
    }
}
declare module '@/Layouts/AuthenticatedLayout.vue' {
    import { DefineComponent } from 'vue';

    const component: DefineComponent<
        {}, // Props
        {}, // Bindings to template setup
        any, // State / Data
        {}, // Computed
        {}, // Methods
        {}, // Mixins / Extends
        {}, // Emits
        {}, // Slots
        string,
        any,
        any,
        {
            header?: () => any;
            default?: () => any;
        }
    >;

    export default component;
}