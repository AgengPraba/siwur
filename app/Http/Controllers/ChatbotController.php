<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Menerima pertanyaan dari frontend, meneruskannya ke AI service,
     * dan mengembalikan jawaban.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ask(Request $request)
    {
        // Increase execution time limit for AI processing
        set_time_limit(60);
        
        // Log untuk debugging
        Log::info('Chatbot request received:', $request->all());
        
        $request->validate([
            'question' => 'required|string|max:2048',
            'session_id' => 'required|string',
            'toko_id' => 'nullable|integer|exists:toko,id',
        ]);

        try {
            // 1. Ambil data dari request
            $question = $request->input('question');
            $sessionId = $request->input('session_id');

            // 2. Dapatkan toko_id dari user yang sedang login
            $user = Auth::user();
            Log::info('User info:', ['user_id' => $user->id, 'user_name' => $user->name]);
            
            $tokoId = $request->input('toko_id');

            if (!$tokoId) {
                $tokoId = session('toko_id') ?? session('current_toko_id');
                if ($tokoId) {
                    Log::info('Toko ID resolved from session', ['toko_id' => $tokoId]);
                }
            }

            if (!$tokoId) {
                $akses = $user->akses()->first();
                if ($akses && $akses->toko_id) {
                    $tokoId = $akses->toko_id;
                    Log::info('Toko ID from akses relation', ['toko_id' => $tokoId]);
                }
            }

            if (!$tokoId) {
                $tokoId = $user->toko()->value('id');
                if ($tokoId) {
                    Log::info('Toko ID from owned toko', ['toko_id' => $tokoId]);
                }
            }

            if (!$tokoId) {
                Log::error('Toko ID tidak ditemukan', [
                    'user_id' => $user->id,
                    'session_keys' => ['toko_id' => session('toko_id'), 'current_toko_id' => session('current_toko_id')],
                ]);
                return response()->json([
                    'error' => 'Toko belum dipilih. Silakan pilih toko terlebih dahulu sebelum menggunakan chatbot.',
                ], 422);
            }

            $tokoId = (int) $tokoId;

                        // Simpan pesan user ke database
            ChatMessage::create([
                'session_id' => $sessionId,
                'toko_id' => $tokoId,
                'role' => 'user',
                'content' => $question,
            ]);

            // 3. Proxy request ke FastAPI AI Service
            $fastApiServiceUrl = config('services.ai_service.url') . '/chat';
            Log::info('Sending request to AI service:', [
                'url' => $fastApiServiceUrl,
                'payload' => [
                    'question' => $question,
                    'tenant_id' => (string) $tokoId
                ]
            ]);

            $response = Http::timeout(60)->post($fastApiServiceUrl, [
                'question' => $question,
                'tenant_id' => (string) $tokoId, // Tetap kirim sebagai tenant_id ke Python API
            ]);

            Log::info('AI Service response:', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                Log::error('AI Service request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json(['error' => 'Gagal menghubungi AI service.'], 502);
            }

            $data = $response->json();
            $answer = $data['answer'] ?? 'Maaf, saya tidak dapat memproses jawaban saat ini.';
            
            Log::info('Extracted answer from AI service:', ['answer' => $answer]);

                        // 4. Simpan jawaban bot ke database
            ChatMessage::create([
                'session_id' => $sessionId,
                'toko_id' => $tokoId,
                'role' => 'bot',
                'content' => $answer,
            ]);

            // 5. Kembalikan jawaban ke frontend
            Log::info('Returning response to frontend:', ['answer' => $answer]);
            return response()->json(['answer' => $answer]);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan internal.'], 500);
        }
    }
}
