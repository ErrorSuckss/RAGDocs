<script setup>
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';


const props = defineProps({
    files: Object
});

const nextPage = () => {
    if (props.files.links.next){
        router.visit(props.files.links.next)
    }
}

const prevPage = () => {
    if (props.files.links.prev){
        router.visit(props.files.links.prev)
    }
}

window.Echo
    .channel('files')
    .listen('.status.updated', (e) => {
        const file = files.value.find(file => file.id === e.id)

        if(file){
            file.status = e.status
        }
    });

const files = ref([...props.files.data]);
const objFiles = ref(props.files);

const handleDelete = async(id)=> {
    try{
        const response = await axios.delete('/delete/'+id);

        if (response.status === 200){
            files.value = files.value.filter(file => file.id !== id)
            objFiles.value.meta.total -= 1
        }
    }catch(error){
        console.log(error.response)
    }
}


const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
    processing: 'bg-blue-100 text-blue-800 ring-blue-600/20',
    completed: 'bg-green-100 text-green-800 ring-green-600/20',
    failed: 'bg-red-100 text-red-800 ring-red-600/20',
}

const statusText = {
    processing: 'Vectorizing',
    completed: 'Synced'
}

const mimeLabels = {
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document": "DOCX",
  "application/pdf": "PDF",
}
</script>

<template>
    <div class="flex min-h-screen bg-slate-50">

        <Sidebar />

        <!-- Main -->
        <div class="flex-1">
            <div class="mx-auto max-w-6xl space-y-8 px-6 py-8">

                <!-- Page heading -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">RAG Document Manager</h1>
                        <p class="mt-1 text-sm text-slate-500">Manage your documents.</p>
                    </div>
                </div>

                <!-- Files table -->
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">
                                Uploaded Documents
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{ files.length }} file{{ files.length === 1 ? '' : 's' }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        File Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        File Path
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        File Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="files.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                                <path d="M14 2v6h6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <p class="mt-3 text-sm font-medium text-slate-700">
                                            No files uploaded
                                        </p>
                                        <p class="mt-1 text-sm text-slate-400">
                                            Upload your first document to get started.
                                        </p>
                                    </td>
                                </tr>
                                <tr v-for="file in files" :key="file.id" class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-500">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                                    <path d="M14 2v6h6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-slate-800">
                                                {{ file.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="max-w-xs truncate px-6 py-4 font-mono text-xs text-slate-500">
                                        {{ file.file_path }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-block max-w-xs truncate rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">
                                            {{ mimeLabels[file.file_type] ?? file.file_type }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset capitalize"
                                            :class="statusClasses[file.status]">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ statusText[file.status] ?? file.status  }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex justify-end gap-1.5">
                                            <a
                                                :href="file.file_path"
                                                target="_blank"
                                                class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                                title="View">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6" />
                                                </svg>
                                            </a>
                                            <button
                                                class="rounded-md p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                                title="Share">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="18" cy="5" r="2.5" stroke="currentColor" stroke-width="1.6" />
                                                    <circle cx="6" cy="12" r="2.5" stroke="currentColor" stroke-width="1.6" />
                                                    <circle cx="18" cy="19" r="2.5" stroke="currentColor" stroke-width="1.6" />
                                                    <path d="m8.2 10.8 7.6-4.1M8.2 13.2l7.6 4.1" stroke="currentColor" stroke-width="1.6" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="handleDelete(file.id)"
                                                class="rounded-md p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                                                title="Delete">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4 7h16M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m-8 0v12a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V7"
                                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 px-6 py-3.5">
                        <p class="text-xs text-slate-500">
                            {{ objFiles.meta.total }} file{{ files.length === 1 ? '' : 's' }} total
                        </p>
                        <div class="flex gap-2">
                            <button class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50
                            disabled:bg-slate-100
                            disabled:text-slate-400
                            disabled:border-slate-200
                            disabled:cursor-not-allowed
                            disabled:opacity-60
                            disabled:hover:bg-slate-100
                            "
                            @click="prevPage"
                            :disabled="!props.files.links.prev"
                            >
                                Previous
                            </button>
                            <button @click="nextPage" :disabled="!props.files.links.next" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 
                            disabled:bg-slate-100
                            disabled:text-slate-400
                            disabled:border-slate-200
                            disabled:cursor-not-allowed
                            disabled:opacity-60
                            disabled:hover:bg-slate-100"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>