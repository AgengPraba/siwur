<div x-data="{ 
        open: localStorage.getItem('chatbot-open') === 'true',
        init() {
            this.$watch('open', value => {
                localStorage.setItem('chatbot-open', value);
            });
        }
    }" 
    class="fixed bottom-4 right-4 z-50" 
    @keydown.window.escape="open = false">
    
    <input type="checkbox" id="chatbot-toggle" class="peer sr-only" x-model="open">

    <label for="chatbot-toggle" class="btn btn-primary btn-circle w-16 h-16 text-2xl shadow-lg flex items-center justify-center">
        <x-icon name="o-chat-bubble-left-right" class="w-7" />
    </label>

    <div x-show="open" x-transition class="mt-4 w-80 sm:w-96 bg-base-100 rounded-xl shadow-2xl border border-base-200 overflow-hidden" wire:ignore.self>
        <div class="flex items-center justify-between px-4 py-3 border-b border-base-200 bg-base-300/60">
            <h3 class="text-lg font-semibold">Asisten AI</h3>
            <div class="flex items-center gap-1">
                <button type="button" class="btn btn-sm btn-ghost" wire:click="clearChat" title="Hapus Chat">
                    <x-icon name="o-trash" class="w-4" />
                </button>
                <button type="button" class="btn btn-sm btn-ghost" @click="open = false">
                    <x-icon name="o-x-mark" class="w-5" />
                </button>
            </div>
        </div>

        <div class="flex flex-col h-[26rem]">
            {{-- Area Pesan --}}
            {{-- Ganti bagian ini --}}
            <div class="flex-grow overflow-y-auto p-4 space-y-4" id="chat-container">
                @foreach ($messages as $message)
                    <div class="chat {{ $message['role'] == 'bot' ? 'chat-start' : 'chat-end' }}">
                        <div class="chat-header">
                            {{ $message['role'] == 'bot' ? 'Siwur@bot' : auth()->user()->name }}
                            <time class="text-xs opacity-50">{{ $message['time'] ?? now()->format('H:i') }}</time>
                        </div>
                        <div class="chat-bubble {{ $message['role'] == 'bot' ? '' : 'chat-bubble-primary' }}">
                            {!! nl2br(e($message['content'])) !!}
                        </div>
                    </div>
                @endforeach

                {{-- Tambahkan indikator "mengetik" ini --}}
                @if ($isBotTyping)
                    <div class="chat chat-start">
                        <div class="chat-bubble">
                            <span class="loading loading-dots loading-md"></span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Form Input --}}
            <hr />
            <div class="p-4">
                <form wire:submit.prevent="sendMessage" wire:loading.attr="disabled">
                    @csrf
                    <x-form>
                        <x-input wire:model="question" placeholder="Ketik pertanyaan Anda..." autofocus>
                            <x-slot:append>
                                <x-button type="submit" icon="o-paper-airplane" class="btn-primary"
                                    spinner="sendMessage">
                                    Kirim
                                </x-button>
                            </x-slot:append>
                        </x-input>
                    </x-form>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const chatContainer = document.getElementById('chat-container');

            const scrollToBottom = () => {
                if (!chatContainer) return;
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            scrollToBottom();

            const observer = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    if (mutation.type === 'childList') {
                        scrollToBottom();
                    }
                }
            });

            observer.observe(chatContainer, {
                childList: true
            });

            @this.on('message-sent', () => {
                setTimeout(scrollToBottom, 100);
            });
            @this.on('get-bot-answer', () => {
                setTimeout(scrollToBottom, 100);
            });
        });
    </script>
</div>
