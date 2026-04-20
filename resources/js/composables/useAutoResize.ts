import { type Ref, onMounted, watch } from 'vue';

export function useAutoResize(el: Ref<HTMLTextAreaElement | null>, value: Ref<string>) {
    function resize() {
        if (!el.value) return;
        el.value.style.height = 'auto';
        el.value.style.height = el.value.scrollHeight + 'px';
    }

    onMounted(resize);
    watch(value, resize);
}
