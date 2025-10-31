<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
          <h1 class="text-4xl font-bold text-gray-800 mb-2">
            AI-Powered Search Assistant
          </h1>
          <p class="text-gray-600">
            Ask questions and get answers from our knowledge base
          </p>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <!-- Messages Area -->
          <div
            ref="messagesContainer"
            class="h-[500px] overflow-y-auto p-6 space-y-4"
          >
            <div v-if="messages.length === 0" class="text-center text-gray-400 mt-20">
              <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
              <p class="text-lg">Start a conversation by asking a question</p>
            </div>

            <div
              v-for="(message, index) in messages"
              :key="index"
              class="flex"
              :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
            >
              <div
                class="max-w-[80%] rounded-2xl px-4 py-3"
                :class="message.role === 'user'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 text-gray-800'"
              >
                <div class="text-sm font-semibold mb-1" :class="message.role === 'user' ? 'text-indigo-200' : 'text-gray-600'">
                  {{ message.role === 'user' ? 'You' : 'Assistant' }}
                </div>
                <div class="whitespace-pre-wrap">{{ message.content }}</div>

                <!-- Sources -->
                <div v-if="message.sources && message.sources.length > 0" class="mt-3 pt-3 border-t border-gray-200">
                  <div class="text-xs font-semibold text-gray-600 mb-2">Sources:</div>
                  <div class="space-y-1">
                    <a
                      v-for="(source, idx) in message.sources"
                      :key="idx"
                      :href="source.url"
                      target="_blank"
                      class="block text-xs text-indigo-600 hover:underline"
                    >
                      📄 {{ source.title }}
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="loading" class="flex justify-start">
              <div class="bg-gray-100 rounded-2xl px-4 py-3">
                <div class="flex space-x-2">
                  <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                  <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                  <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Input Area -->
          <div class="border-t border-gray-200 p-4 bg-gray-50">
            <form @submit.prevent="sendMessage" class="flex gap-2">
              <input
                v-model="inputMessage"
                type="text"
                placeholder="Ask a question..."
                class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                :disabled="loading"
              />
              <button
                type="submit"
                :disabled="!inputMessage.trim() || loading"
                class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors font-medium"
              >
                <span v-if="!loading">Send</span>
                <span v-else>...</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Example Questions -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600 mb-3">Try asking:</p>
          <div class="flex flex-wrap justify-center gap-2">
            <button
              v-for="example in exampleQuestions"
              :key="example"
              @click="inputMessage = example"
              class="px-4 py-2 bg-white text-sm text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 transition-colors shadow-sm"
            >
              {{ example }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const config = useRuntimeConfig()
const inputMessage = ref('')
const messages = ref([])
const loading = ref(false)
const messagesContainer = ref(null)

const exampleQuestions = [
  'What services do you offer?',
  'Tell me about your company',
  'How can I contact you?',
  'What are your latest blog posts?'
]

const sendMessage = async () => {
  if (!inputMessage.value.trim() || loading.value) return

  const userMessage = inputMessage.value.trim()
  inputMessage.value = ''

  // Add user message
  messages.value.push({
    role: 'user',
    content: userMessage
  })

  // Scroll to bottom
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })

  loading.value = true

  try {
    const response = await $fetch(`${config.public.apiBase}/api/chatbot/chat`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: userMessage
      })
    })

    if (response.success) {
      messages.value.push({
        role: 'assistant',
        content: response.response,
        sources: response.sources || []
      })
    } else {
      messages.value.push({
        role: 'assistant',
        content: 'Sorry, I encountered an error. Please try again.'
      })
    }
  } catch (error) {
    console.error('Error:', error)
    messages.value.push({
      role: 'assistant',
      content: 'Sorry, I could not connect to the server. Please make sure the backend is running.'
    })
  } finally {
    loading.value = false

    // Scroll to bottom
    nextTick(() => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
      }
    })
  }
}
</script>
