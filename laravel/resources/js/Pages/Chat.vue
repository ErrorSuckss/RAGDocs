<script setup>
	import Sidebar from '@/Components/Sidebar.vue';
	import { nextTick, ref } from 'vue';

	const chatInput = ref('')
	const chatContainer = ref(null)
	const isSending = ref(false)
	const inputRef = ref(null)

	const messages = ref([
		{ from: 'ai', text: 'Hi, how can I help you?' }
	]);

	const scrollToBottom = async()=>{
		await nextTick();

		if(chatContainer.value){
			chatContainer.value.scrollTop = chatContainer.value.scrollHeight
		}
	}

	const sendMessage = async()=>{
		const text = chatInput.value.trim()
		if(!text || isSending.value) return

		messages.value.push({from:'user', text})
		chatInput.value = ''
		isSending.value = true
		scrollToBottom()

		try{
			const response = await axios.post('/query', {query: text});

			if(response.status === 200){
				const answer = response.data.response
				console.log(response)
				console.log('answer: ' + answer)
				messages.value.push({from:'ai', text: answer})
			}
		}catch(error){
			console.log(error)
			messages.value.push({ from: 'ai', text: 'Sorry, something went wrong.' });
		}finally{
			scrollToBottom()
			isSending.value = false

			await nextTick();
			inputRef.value?.focus()
		}
	}


</script>

<template>
	<div class="flex h-screen bg-slate-50">

		<Sidebar />

		<!-- Main -->
		<div class="flex flex-1 flex-col overflow-hidden">
			<!-- Chat page body -->
			<div class="flex flex-1 flex-col overflow-hidden px-6 py-6">

				<!-- Page heading -->
				<div class="mb-4 flex items-center justify-between">
					<div>
						<h1 class="text-2xl font-bold text-slate-900">Chat with Your Documents</h1>
						<p class="mt-1 text-sm text-slate-500">Ask questions and get answers powered by your uploaded data.</p>
					</div>
					<button
						class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
						<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
						</svg>
						New Chat
					</button>
				</div>

				<!-- Chat card -->
				<div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
					<!-- Messages -->
					<div ref="chatContainer" class="flex-1 space-y-4 overflow-y-auto bg-slate-50/50 px-5 py-5">
						<template v-for="(msg,index) in messages" :key="index">
							<div v-if="msg.from === 'ai'" class="flex items-start gap-2.5">
								<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
									<svg viewBox="0 0 24 24" id="Artwork" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" 	stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M11.26,6.86l-3.39-2a.75.75,0,1,1,.78-1.28l3.36,2Z" style="fill:#aecbfa;fill-rule:evenodd"></path><path d="M11.52,10.31,4.69,6.36a.75.75,0,0,1,.75-1.3l6.08,3.52Z" style="fill:#aecbfa;fill-rule:evenodd"></path><path d="M19.6,5.33a.76.76,0,0,0-1-.27L12.75,8.4V6.85l3.39-2a.74.74,0,0,0,.25-1,.75.75,0,0,0-1-.25L11.65,5.78a.79.79,0,0,0-.39.68V21a.75.75,0,1,0,1.49,0V10.14l6.57-3.78A.76.76,0,0,0,19.6,5.33Z" style="fill:#669df6;fill-rule:evenodd"></path><circle cx="12.01" cy="3.09" r="0.84" style="fill:#669df6"></circle><path d="M9.48,10.82a.73.73,0,0,1,.38.64v4.08a.74.74,0,0,1-.38.65.79.79,0,0,1-.37.1.86.86,0,0,1-.38-.1L4.41,13.7a.75.75,0,1,1,.75-1.3l3.2,1.84V11.9l-1.45-.83a.75.75,0,0,1-.27-1,.74.74,0,0,1,1-.27Z" style="fill:#aecbfa"></path><circle cx="5.26" cy="9.26" r="0.84" style="fill:#aecbfa"></circle><path d="M16.34,9.77a.74.74,0,0,1,1,.27.75.75,0,0,1-.27,1l-1.45.83v2.34l3.2-1.84a.75.75,0,1,1,.75,1.3l-4.32,2.49a.86.86,0,0,1-.38.1.79.79,0,0,1-.37-.1.74.74,0,0,1-.38-.65V11.46a.73.73,0,0,1,.38-.64Z" style="fill:#4285f4"></path><circle cx="18.74" cy="9.26" r="0.84" style="fill:#4285f4"></circle></g>
									</svg>
								</div>
								<div class="max-w-lg rounded-lg rounded-tl-sm bg-indigo-50 px-4 py-2.5 text-sm text-slate-700 shadow-sm">
									{{ msg.text }}
								</div>
							</div>
							<div v-else class="flex justify-end">
								<div class="max-w-lg rounded-lg rounded-tr-sm bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm ring-1 ring-slate-200">
									{{ msg.text }}
								</div>
							</div>
						</template>

						<div v-if="isSending" class="flex items-start gap-2.5">
							<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
								<svg viewBox="0 0 24 24" id="Artwork" xmlns="http://www.w3.org/2000/svg" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M11.26,6.86l-3.39-2a.75.75,0,1,1,.78-1.28l3.36,2Z" style="fill:#aecbfa;fill-rule:evenodd"></path><path d="M11.52,10.31,4.69,6.36a.75.75,0,0,1,.75-1.3l6.08,3.52Z" style="fill:#aecbfa;fill-rule:evenodd"></path><path d="M19.6,5.33a.76.76,0,0,0-1-.27L12.75,8.4V6.85l3.39-2a.74.74,0,0,0,.25-1,.75.75,0,0,0-1-.25L11.65,5.78a.79.79,0,0,0-.39.68V21a.75.75,0,1,0,1.49,0V10.14l6.57-3.78A.76.76,0,0,0,19.6,5.33Z" style="fill:#669df6;fill-rule:evenodd"></path><circle cx="12.01" cy="3.09" r="0.84" style="fill:#669df6"></circle><path d="M9.48,10.82a.73.73,0,0,1,.38.64v4.08a.74.74,0,0,1-.38.65.79.79,0,0,1-.37.1.86.86,0,0,1-.38-.1L4.41,13.7a.75.75,0,1,1,.75-1.3l3.2,1.84V11.9l-1.45-.83a.75.75,0,0,1-.27-1,.74.74,0,0,1,1-.27Z" style="fill:#aecbfa"></path><circle cx="5.26" cy="9.26" r="0.84" style="fill:#aecbfa"></circle><path d="M16.34,9.77a.74.74,0,0,1,1,.27.75.75,0,0,1-.27,1l-1.45.83v2.34l3.2-1.84a.75.75,0,1,1,.75,1.3l-4.32,2.49a.86.86,0,0,1-.38.1.79.79,0,0,1-.37-.1.74.74,0,0,1-.38-.65V11.46a.73.73,0,0,1,.38-.64Z" style="fill:#4285f4"></path><circle cx="18.74" cy="9.26" r="0.84" style="fill:#4285f4"></circle></g></svg>
							</div>
							<div class="max-w-lg rounded-lg rounded-tl-sm bg-indigo-50 px-4 py-2.5 text-sm italic text-slate-500 shadow-sm">
								Typing...
							</div>
						</div>
					</div>

					<!-- Input bar -->
					<div class="border-t border-slate-100 px-5 py-4">
						<div class="flex items-center gap-2">
							<input
								ref="inputRef"
								v-model="chatInput"
								@keyup.enter="sendMessage"
								:disabled="isSending"
								type="text"
								placeholder="Ask a question about your documents..."
								class="min-w-0 flex-1 rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:bg-slate-50" />

							<button
								@click="sendMessage"
								:disabled="isSending"
								class="inline-flex shrink-0 items-center gap-1.5 rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-slate-300">
								Send
							</button>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</template>