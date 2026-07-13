<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const navItems = [
    {
        name: 'Dashboard',
        href: '/',
        icon: 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        iconType: 'rect',
    },
    {
        name: 'Documents',
        href: '/documents',
        icon: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6ZM14 2v6h6',
        iconType: 'path',
    },
    {
        name: 'AI Chat',
        href: '/chat',
        icon: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z',
        iconType: 'path',
    },
];

const page = usePage();

const currentPath = computed(() => page.url.split('?')[0]);

const isActive = (href) => {
    if (href === '/') {
        return currentPath.value === '/';
    }
    return currentPath.value === href || currentPath.value.startsWith(href + '/');
};
</script>

<template>
    <aside class="sticky top-0 hidden h-screen w-60 shrink-0 overflow-hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
        <div class="flex items-center gap-2 px-6 py-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600">
                <svg viewBox="0 0 24 24" id="Artwork" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" 	stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M11.26,6.86l-3.39-2a.75.75,0,1,1,.78-1.28l3.36,2Z" style="fill:#aecbfa;fill-rule:evenodd"></path><path d="M11.52,10.31,4.69,6.36a.75.75,0,0,1,.75-1.3l6.08,3.52Z" style="fill:#aecbfa;fill-rule:evenodd"></path><path d="M19.6,5.33a.76.76,0,0,0-1-.27L12.75,8.4V6.85l3.39-2a.74.74,0,0,0,.25-1,.75.75,0,0,0-1-.25L11.65,5.78a.79.79,0,0,0-.39.68V21a.75.75,0,1,0,1.49,0V10.14l6.57-3.78A.76.76,0,0,0,19.6,5.33Z" style="fill:#669df6;fill-rule:evenodd"></path><circle cx="12.01" cy="3.09" r="0.84" style="fill:#669df6"></circle><path d="M9.48,10.82a.73.73,0,0,1,.38.64v4.08a.74.74,0,0,1-.38.65.79.79,0,0,1-.37.1.86.86,0,0,1-.38-.1L4.41,13.7a.75.75,0,1,1,.75-1.3l3.2,1.84V11.9l-1.45-.83a.75.75,0,0,1-.27-1,.74.74,0,0,1,1-.27Z" style="fill:#aecbfa"></path><circle cx="5.26" cy="9.26" r="0.84" style="fill:#aecbfa"></circle><path d="M16.34,9.77a.74.74,0,0,1,1,.27.75.75,0,0,1-.27,1l-1.45.83v2.34l3.2-1.84a.75.75,0,1,1,.75,1.3l-4.32,2.49a.86.86,0,0,1-.38.1.79.79,0,0,1-.37-.1.74.74,0,0,1-.38-.65V11.46a.73.73,0,0,1,.38-.64Z" style="fill:#4285f4"></path><circle cx="18.74" cy="9.26" r="0.84" style="fill:#4285f4"></circle></g>
                </svg>
            </div>
            <span class="text-sm font-semibold text-slate-900">Rag Docs</span>
        </div>

        <nav class="flex-1 space-y-1 px-3 py-2">
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="item.href"
                class="flex items-center gap-2.5 rounded-md px-3 py-2 text-sm font-medium transition"
                :class="isActive(item.href)
                    ? 'bg-indigo-50 text-indigo-700'
                    : 'text-slate-600 hover:bg-slate-50'"
            >
                <!-- Dashboard uses a 4-square grid icon, others use a single path -->
                <svg v-if="item.iconType === 'rect'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6" />
                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6" />
                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6" />
                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6" />
                </svg>
                <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path :d="item.icon" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" />
                </svg>
                {{ item.name }}

                <!-- Active indicator dot -->
                <span
                    v-if="isActive(item.href)"
                    class="ml-auto h-1.5 w-1.5 rounded-full bg-indigo-600"
                ></span>
            </Link>
        </nav>
    </aside>
</template>