# Skill: Chatbot Controller Implementation

## Deskripsi
Skill ini menjelaskan cara mengintegrasikan RAG chatbot Python dengan Laravel controller untuk sistem SIWUR.

## Arsitektur Integrasi

```
┌─────────────────┐      HTTP/API      ┌─────────────────┐
│  Laravel App    │ ◄────────────────► │  Python FastAPI │
│  (Frontend)     │                    │  (AI Service)   │
└────────┬────────┘                    └────────┬────────┘
         │                                      │
         │ Livewire                             │ RAG
         │                                      │
┌────────▼────────┐                    ┌────────▼────────┐
│  Chatbot        │                    │  Gemini API     │
│  Component      │                    │  + ChromaDB     │
└─────────────────┘                    └─────────────────┘
```

## Laravel Controller

### ChatbotController.php
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;

class ChatbotController extends Controller
{
    protected string $aiServiceUrl;
    
    public function __construct()
    {
        $this->aiServiceUrl = config('services.ai_service.url', 'http://localhost:8000');
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        
        $user = auth()->user();
        $tokoId = $user->toko_id;
        
        // Save user message
        $userMessage = ChatMessage::create([
            'user_id' => $user->id,
            'toko_id' => $tokoId,
            'role' => 'user',
            'content' => $request->message,
        ]);
        
        try {
            // Call AI service
            $response = Http::timeout(60)->post("{$this->aiServiceUrl}/chat", [
                'question' => $request->message,
                'toko_id' => $tokoId,
                'user_id' => $user->id,
                'conversation_id' => $request->conversation_id,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Save assistant message
                $assistantMessage = ChatMessage::create([
                    'user_id' => $user->id,
                    'toko_id' => $tokoId,
                    'role' => 'assistant',
                    'content' => $data['answer'],
                    'metadata' => json_encode([
                        'datasource' => $data['datasource'] ?? null,
                        'documents_used' => $data['documents_count'] ?? 0,
                    ]),
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $assistantMessage,
                    'datasource' => $data['datasource'] ?? 'unknown',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'AI service error',
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to AI service: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    public function getHistory(Request $request)
    {
        $messages = ChatMessage::where('user_id', auth()->id())
            ->where('toko_id', auth()->user()->toko_id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();
        
        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }
    
    public function clearHistory()
    {
        ChatMessage::where('user_id', auth()->id())
            ->where('toko_id', auth()->user()->toko_id)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared',
        ]);
    }
}
```

## Livewire Component

### ChatbotComponent.php
```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;

class ChatbotComponent extends Component
{
    public string $message = '';
    public array $messages = [];
    public bool $isLoading = false;
    public string $aiServiceUrl;
    
    protected $listeners = ['refreshMessages'];
    
    public function mount()
    {
        $this->aiServiceUrl = config('services.ai_service.url', 'http://localhost:8000');
        $this->loadMessages();
    }
    
    public function loadMessages()
    {
        $this->messages = ChatMessage::where('user_id', auth()->id())
            ->where('toko_id', auth()->user()->toko_id)
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
            ->toArray();
    }
    
    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }
        
        $this->isLoading = true;
        $userMessage = trim($this->message);
        $this->message = '';
        
        // Add user message to UI immediately
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'created_at' => now()->toDateTimeString(),
        ];
        
        // Save to database
        $savedUserMsg = ChatMessage::create([
            'user_id' => auth()->id(),
            'toko_id' => auth()->user()->toko_id,
            'role' => 'user',
            'content' => $userMessage,
        ]);
        
        try {
            // Call AI service
            $response = Http::timeout(60)->post("{$this->aiServiceUrl}/chat", [
                'question' => $userMessage,
                'toko_id' => auth()->user()->toko_id,
                'user_id' => auth()->id(),
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Save assistant message
                $assistantMsg = ChatMessage::create([
                    'user_id' => auth()->id(),
                    'toko_id' => auth()->user()->toko_id,
                    'role' => 'assistant',
                    'content' => $data['answer'],
                    'metadata' => json_encode([
                        'datasource' => $data['datasource'] ?? null,
                    ]),
                ]);
                
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => $data['answer'],
                    'created_at' => now()->toDateTimeString(),
                    'datasource' => $data['datasource'] ?? null,
                ];
            } else {
                $this->messages[] = [
                    'role' => 'assistant',
                    'content' => 'Maaf, terjadi kesalahan. Silakan coba lagi.',
                    'created_at' => now()->toDateTimeString(),
                    'error' => true,
                ];
            }
        } catch (\Exception $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => 'Maaf, tidak dapat terhubung ke AI service.',
                'created_at' => now()->toDateTimeString(),
                'error' => true,
            ];
        }
        
        $this->isLoading = false;
        $this->dispatch('scroll-to-bottom');
    }
    
    public function clearChat()
    {
        ChatMessage::where('user_id', auth()->id())
            ->where('toko_id', auth()->user()->toko_id)
            ->delete();
        
        $this->messages = [];
    }
    
    public function render()
    {
        return view('livewire.chatbot-component');
    }
}
```

### chatbot-component.blade.php
```blade
<div class="flex flex-col h-full">
    <!-- Chat Messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
        @foreach($messages as $msg)
            <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-lg p-3 
                    {{ $msg['role'] === 'user' 
                        ? 'bg-blue-500 text-white' 
                        : 'bg-gray-100 text-gray-800' }}">
                    <p class="whitespace-pre-wrap">{{ $msg['content'] }}</p>
                    @if(isset($msg['datasource']))
                        <span class="text-xs opacity-70 mt-1 block">
                            Sumber: {{ $msg['datasource'] }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
        
        @if($isLoading)
            <div class="flex justify-start">
                <div class="bg-gray-100 rounded-lg p-3">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Input Area -->
    <div class="border-t p-4">
        <form wire:submit="sendMessage" class="flex space-x-2">
            <input 
                type="text" 
                wire:model="message"
                placeholder="Ketik pertanyaan Anda..."
                class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @disabled($isLoading)
            />
            <button 
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 disabled:opacity-50"
                @disabled($isLoading)
            >
                Kirim
            </button>
        </form>
    </div>
</div>

@script
<script>
    $wire.on('scroll-to-bottom', () => {
        const container = document.getElementById('chat-messages');
        container.scrollTop = container.scrollHeight;
    });
</script>
@endscript
```

## Route Configuration

### routes/web.php
```php
use App\Http\Controllers\ChatbotController;

Route::middleware(['auth'])->group(function () {
    Route::post('/api/chatbot/send', [ChatbotController::class, 'sendMessage']);
    Route::get('/api/chatbot/history', [ChatbotController::class, 'getHistory']);
    Route::delete('/api/chatbot/history', [ChatbotController::class, 'clearHistory']);
});
```

## Configuration

### config/services.php
```php
return [
    // ... other services
    
    'ai_service' => [
        'url' => env('AI_SERVICE_URL', 'http://localhost:8000'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 60),
    ],
];
```

### .env
```
AI_SERVICE_URL=http://localhost:8000
AI_SERVICE_TIMEOUT=60
GEMINI_API_KEY=your_gemini_api_key
```

## Database Migration untuk Chat Messages

```php
// database/migrations/xxxx_create_chat_messages_table.php
Schema::create('chat_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('toko_id')->constrained('toko');
    $table->enum('role', ['user', 'assistant', 'system']);
    $table->text('content');
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'toko_id', 'created_at']);
});
```

## Error Handling

```php
// Exception handler untuk AI service errors
class AiServiceException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'error' => 'AI Service unavailable',
            'message' => $this->getMessage(),
        ], 503);
    }
}
```

## Rate Limiting

```php
// app/Http/Middleware/ChatRateLimiter.php
class ChatRateLimiter
{
    public function handle($request, Closure $next)
    {
        $key = 'chat:' . auth()->id();
        
        if (RateLimiter::tooManyAttempts($key, 30)) { // 30 messages per minute
            return response()->json([
                'success' => false,
                'error' => 'Too many requests. Please wait.',
            ], 429);
        }
        
        RateLimiter::hit($key, 60);
        
        return $next($request);
    }
}
```

## Best Practices

1. **Async processing** - Untuk response panjang, gunakan queue
2. **Caching** - Cache response untuk pertanyaan yang sama
3. **Rate limiting** - Batasi jumlah request per user
4. **Error handling** - Handle AI service downtime gracefully
5. **Logging** - Log semua interaksi untuk analytics
6. **Security** - Validasi input, sanitize output
