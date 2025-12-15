<?php

namespace App\Livewire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;


class ChatbotComponent extends Component
{
    public array $messages = [];
    public string $question = '';
    public string $sessionId;
    public bool $isBotTyping = false; // Properti baru untuk status "mengetik"
    public int $totalTokensUsed = 0;

    public function mount()
    {
        // Ambil session ID dari session atau buat baru
        $this->sessionId = Session::get('chatbot_session_id', (string) Str::uuid());
        Session::put('chatbot_session_id', $this->sessionId);
        
        // Ambil messages dari session jika ada
        $this->messages = Session::get('chatbot_messages', []);
        
        // Jika belum ada pesan, tambahkan pesan pembuka
        if (empty($this->messages)) {
            $this->messages[] = [
                'role' => 'bot',
                'content' => 'Halo! Saya asisten AI Anda. Ada yang bisa saya bantu?',
                'time' => now()->format('H:i')
            ];
            Session::put('chatbot_messages', $this->messages);
        }
    }

    public function sendMessage()
    {
        if (empty($this->question)) {
            return;
        }

        // 1. Tambahkan pesan user ke UI secara instan
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->question,
            'time' => now()->format('H:i')
        ];
        
        // Simpan ke session
        Session::put('chatbot_messages', $this->messages);

        // Simpan pertanyaan sebelum di-reset
        $userQuestion = $this->question;
        $this->reset('question');

        // Tampilkan indikator "mengetik"
        $this->isBotTyping = true;

        // 2. Panggil metode lain untuk memproses AI di latar belakang
        $this->dispatch('get-bot-answer', question: $userQuestion);
    }

    #[On('get-bot-answer')] // Atribut ini akan membuat metode "mendengarkan" event
    public function getBotAnswer(string $question)
    {
        try {
            $request = new Request([
                'question' => $question,
                'session_id' => $this->sessionId,
            ]);

            // Buat instance controller dan panggil metodenya
            $chatbotController = new ChatbotController();
            $response = $chatbotController->ask($request);


            // Cek jika response adalah JsonResponse dan sukses
            if ($response->isSuccessful()) {
                $answer = json_decode($response->getContent(), true)['answer'];
            } else {
                $errorMessage = json_decode($response->getContent(), true)['error'] ?? 'Gagal memproses permintaan.';
                $answer = 'Error: ' . $errorMessage;
                Log::error('Chatbot controller error:', ['status' => $response->getStatusCode(), 'body' => $response->getContent()]);
            }
        } catch (\Exception $e) {
            $answer = 'Terjadi kesalahan fatal saat menghubungi server. Silakan periksa log.';
            Log::error('Chatbot fatal exception: ' . $e->getMessage());
        }

        // Sembunyikan indikator "mengetik"
        $this->isBotTyping = false;

        // Tambahkan jawaban bot ke UI
        $this->messages[] = [
            'role' => 'bot',
            'content' => $answer,
            'time' => now()->format('H:i')
        ];
        
        // Simpan ke session
        Session::put('chatbot_messages', $this->messages);
    }
    
    /**
     * Clear chat history
     */
    public function clearChat()
    {
        $this->messages = [
            [
                'role' => 'bot',
                'content' => 'Halo! Saya asisten AI Anda. Ada yang bisa saya bantu?',
                'time' => now()->format('H:i')
            ]
        ];
        $this->sessionId = (string) Str::uuid();
        Session::put('chatbot_messages', $this->messages);
        Session::put('chatbot_session_id', $this->sessionId);
    }

    public function render()
    {
        return view('livewire.chatbot-component');
    }
}