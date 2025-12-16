<?php

namespace App\Livewire\Penjualan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PembayaranPenjualan;
use App\Models\TransaksiGudangStock;
use App\Models\GudangStock;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\PembelianDetail;
use App\Models\ModalKasir;
use App\Models\AturanHargaBarang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Traits\LivewireTenancy;

#[Title('Form Penjualan')]
class Form extends Component
{
    use Toast, LivewireTenancy;

    public $breadcrumbs;
    protected $listeners = [
        'pos:add-item' => 'handleShortcutAddItem',
        'pos:select-payment-type' => 'handleShortcutSelectPaymentType',
        'pos:fill-full-payment' => 'handleShortcutFillFullPayment',
        'pos:set-quick-cash' => 'handleShortcutSetQuickCash',
        'pos:set-suggested-payment' => 'handleShortcutSetSuggestedPayment',
        'pos:add-payment' => 'handleShortcutAddPayment',
        'pos:save-transaction' => 'handleShortcutSaveTransaction',
        'pos:pay-and-save' => 'handleShortcutPayAndSave',
        'pos:auto-settle-print-redirect' => 'handleShortcutAutoSettlePrintRedirect',
        'pos:clear-barcode' => 'clearBarcodeForm',
    ];
    // Hydration optimization methods
    public function hydrate()
    {
        try {
            // Log hydration start
            $detailsCountBefore = count($this->details ?? []);
            Log::info('Hydrate started', [
                'user_id' => $this->user_id,
                'type' => $this->type,
                'details_count_before' => $detailsCountBefore
            ]);
             //
            // Ensure arrays are properly initialized with safe defaults
            $this->details = is_array($this->details) ? $this->details : [];
            $this->payments = is_array($this->payments) ? $this->payments : [];
            $this->satuan_data = is_array($this->satuan_data) ? $this->satuan_data : [];
            $this->pembelian_detail_data = is_array($this->pembelian_detail_data) ? $this->pembelian_detail_data : [];

            // Sanitize numeric values with better validation
            $this->total_harga = $this->sanitizeNumeric($this->total_harga, 0);
            $this->detail_harga_satuan = $this->sanitizeNumeric($this->detail_harga_satuan, 0);
            $this->detail_harga_beli = $this->sanitizeNumeric($this->detail_harga_beli, 0);
            $this->detail_jumlah = $this->sanitizeNumeric($this->detail_jumlah, 1, 0.01);
            $this->detail_jumlah_tersedia = $this->sanitizeNumeric($this->detail_jumlah_tersedia, 0);
            $this->detail_diskon = $this->sanitizeNumeric($this->detail_diskon, 0);
            $this->detail_biaya_lain = $this->sanitizeNumeric($this->detail_biaya_lain, 0);
            $this->payment_jumlah = $this->sanitizeNumeric($this->payment_jumlah, 0);

            // Validate entire component state after hydration
            $this->validateComponentState();

            // Enhanced session recovery for create mode
            if ($this->type === 'create' && $this->user_id) {
                $currentDetailsCount = count($this->details ?? []);
                $sessionKey = 'penjualan_temp_' . $this->user_id;
                $sessionData = session()->get($sessionKey);
                
                // Check if we need to reload from session
                $shouldReload = false;
                if ($currentDetailsCount === 0 && $sessionData && isset($sessionData['details']) && !empty($sessionData['details'])) {
                    $shouldReload = true;
                    Log::info('Details empty, reloading from session', [
                        'user_id' => $this->user_id,
                        'session_details_count' => count($sessionData['details'])
                    ]);
                } elseif ($sessionData && isset($sessionData['details']) && count($sessionData['details']) > $currentDetailsCount) {
                    $shouldReload = true;
                    Log::info('Session has more details than component, reloading', [
                        'user_id' => $this->user_id,
                        'component_details_count' => $currentDetailsCount,
                        'session_details_count' => count($sessionData['details'])
                    ]);
                }
                
                if ($shouldReload) {
                    $this->loadFromSession();
                    Log::info('Session reload completed', [
                        'user_id' => $this->user_id,
                        'details_count_after_reload' => count($this->details ?? [])
                    ]);
                }
            }
            
            $detailsCountAfter = count($this->details ?? []);
            Log::info('Hydrate completed', [
                'user_id' => $this->user_id,
                'details_count_before' => $detailsCountBefore,
                'details_count_after' => $detailsCountAfter
            ]);
            
        } catch (\Exception $e) {
            Log::error('Hydration error: ' . $e->getMessage());
            $this->resetToSafeDefaults();
        }
    }

    /**
     * Verify data integrity and attempt recovery if needed
     */
    private function verifyDataIntegrity($context = 'unknown')
    {
        $currentDetailsCount = count($this->details ?? []);
        
        Log::info('Data integrity check', [
            'context' => $context,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'current_details_count' => $currentDetailsCount
        ]);
        
        // For create mode, check if we have session data but no component data
        if ($this->type === 'create' && $this->user_id && $currentDetailsCount === 0) {
            $sessionKey = 'penjualan_temp_' . $this->user_id;
            $sessionData = session()->get($sessionKey);
            
            if ($sessionData && isset($sessionData['details']) && !empty($sessionData['details'])) {
                Log::warning('Data integrity issue detected - recovering from session', [
                    'context' => $context,
                    'user_id' => $this->user_id,
                    'session_details_count' => count($sessionData['details'])
                ]);
                
                $this->loadFromSession();
                
                $recoveredCount = count($this->details ?? []);
                Log::info('Data recovery completed', [
                    'context' => $context,
                    'recovered_details_count' => $recoveredCount
                ]);
                
                return $recoveredCount > 0;
            }
        }
        
        return $currentDetailsCount > 0 || $this->type !== 'create';
    }

    /**
     * Sanitize numeric values with proper validation
     */
    private function sanitizeNumeric($value, $default = 0, $min = null)
    {
        if (is_null($value) || $value === '' || $value === false) {
            return $default;
        }

        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if (!is_numeric($value)) {
            return $default;
        }

        $numericValue = (float) $value;

        if ($min !== null && $numericValue < $min) {
            return $min;
        }

        return $numericValue;
    }

    /**
     * Reset component to safe defaults in case of corruption
     */
    private function resetToSafeDefaults()
    {
        $this->details = [];
        $this->payments = [];
        $this->satuan_data = [];
        $this->pembelian_detail_data = [];
        $this->total_harga = 0;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah = 1;
        $this->detail_jumlah_tersedia = 0;
        $this->payment_jumlah = 0;
        $this->status = 'belum_bayar';
        $this->barcode_search = '';
        $this->show_modal_kasir_form = false;
        $this->modal_kasir_jumlah = 0;
    }

    /**
     * Prevent rapid updates that can cause hydration issues
     */
    private function isRapidUpdate($field)
    {
        $now = microtime(true);
        $key = 'last_update_' . $field;

        if (!isset($this->lastUpdateTimes)) {
            $this->lastUpdateTimes = [];
        }

        if (isset($this->lastUpdateTimes[$key])) {
            $timeDiff = $now - $this->lastUpdateTimes[$key];
            // Prevent updates faster than 100ms (0.1 seconds)
            if ($timeDiff < 0.1) {
                return true;
            }
        }

        $this->lastUpdateTimes[$key] = $now;
        return false;
    }

    public function dehydrate()
    {
        // Log before dehydration
        $detailsCountBefore = count($this->details ?? []);
        Log::info('Dehydrate called', [
            'details_count_before' => $detailsCountBefore,
            'user_id' => $this->user_id
        ]);

        // Clean up and optimize data before serialization
        // Remove any null or invalid entries from arrays
        $this->details = array_filter($this->details ?? [], function ($detail) {
            return is_array($detail) && isset($detail['barang_id']);
        });

        $this->payments = array_filter($this->payments ?? [], function ($payment) {
            return is_array($payment) && isset($payment['jenis_pembayaran']);
        });

        // Reset array indexes
        $this->details = array_values($this->details);
        $this->payments = array_values($this->payments);
        
        // Log after dehydration
        $detailsCountAfter = count($this->details ?? []);
        Log::info('Dehydrate completed', [
            'details_count_before' => $detailsCountBefore,
            'details_count_after' => $detailsCountAfter,
            'user_id' => $this->user_id
        ]);
        
        // Save to session after dehydration to ensure data persistence
        if ($this->type === 'create' && $this->user_id) {
            $this->saveToSession();
        }
    }

    // Method to validate and sanitize component state
    private function validateComponentState()
    {
        try {
            // Ensure user_id is set
            if (!$this->user_id) {
                $this->user_id = Auth::id();
            }

            // Validate and sanitize basic properties
            $this->nomor_penjualan = $this->nomor_penjualan ?? '';
            $this->tanggal_penjualan = $this->tanggal_penjualan ?? Carbon::now()->format('Y-m-d H:i:s');
            $this->customer_id = is_numeric($this->customer_id) ? (int) $this->customer_id : null;

            $this->keterangan = $this->keterangan ?? '';
            $this->status = in_array($this->status, ['belum_bayar', 'belum_lunas', 'lunas']) ? $this->status : 'belum_bayar';

            // Validate arrays
            if (!is_array($this->details)) {
                $this->details = [];
            }
            if (!is_array($this->payments)) {
                $this->payments = [];
            }
            if (!is_array($this->satuan_data)) {
                $this->satuan_data = [];
            }
            if (!is_array($this->pembelian_detail_data)) {
                $this->pembelian_detail_data = [];
            }

            // Validate detail form properties
            $this->detail_barang_id = is_numeric($this->detail_barang_id) ? (int) $this->detail_barang_id : null;
            $this->detail_satuan_id = is_numeric($this->detail_satuan_id) ? (int) $this->detail_satuan_id : null;
            $this->detail_gudang_id = is_numeric($this->detail_gudang_id) ? (int) $this->detail_gudang_id : null;
            $this->detail_pembelian_detail_id = is_numeric($this->detail_pembelian_detail_id) ? (int) $this->detail_pembelian_detail_id : null;
            $this->detail_harga_satuan = is_numeric($this->detail_harga_satuan) ? (float) $this->detail_harga_satuan : 0;
            $this->detail_harga_beli = is_numeric($this->detail_harga_beli) ? (float) $this->detail_harga_beli : 0;
            $this->detail_jumlah = is_numeric($this->detail_jumlah) && $this->detail_jumlah > 0 ? (float) $this->detail_jumlah : 1;
            $this->detail_jumlah_tersedia = is_numeric($this->detail_jumlah_tersedia) ? (float) $this->detail_jumlah_tersedia : 0;

            // Validate payment form properties
            $this->payment_jenis = in_array($this->payment_jenis, ['cash', 'transfer', 'check', 'other']) ? $this->payment_jenis : 'cash';
            $this->payment_jumlah = is_numeric($this->payment_jumlah) ? (float) $this->payment_jumlah : 0;
            $this->payment_keterangan = $this->payment_keterangan ?? '';

            // Validate barcode search
            $this->barcode_search = $this->barcode_search ?? '';

            // Validate modal kasir properties
            $this->show_modal_kasir_form = is_bool($this->show_modal_kasir_form) ? $this->show_modal_kasir_form : false;
            $this->modal_kasir_jumlah = is_numeric($this->modal_kasir_jumlah) ? (float) $this->modal_kasir_jumlah : 0;

            return true;
        } catch (\Exception $e) {
            Log::error('Component state validation failed: ' . $e->getMessage());
            return false;
        }
    }
    protected ?string $postShortcutIntent = null;
    protected ?int $postShortcutPaymentId = null;
    public $type = 'create';
    public $penjualan_ID;
    public $penjualan;
    public $nama_satuan;
    // Header Penjualan
    public $nomor_penjualan;
    public $tanggal_penjualan;
    public $customer_id;
    public $user_id;
    public $keterangan;
    public $total_harga = 0;
    public $status = 'belum_bayar';
    public $kembalian = 0;
    // Options list
    public $getBarangSearch;
    // Barcode scanning
    public $barcode_search = '';
    // Detail Items
    public $details = [];
    public $detail_barang_id;
    public $detail_satuan_id;
    public $detail_gudang_id;
    public $detail_pembelian_detail_id;
    public $detail_harga_satuan = 0;
    public $detail_harga_beli = 0;
    public $detail_jumlah = 1;
    public $detail_jumlah_tersedia = 0;
    public $detail_diskon = 0;
    public $detail_biaya_lain = 0;

    // Pembayaran
    public $payments = [];
    public $payment_jenis = 'cash';
    public $payment_jumlah = 0;
    public $payment_keterangan = '';

    // Data untuk dropdown - converted to computed properties to reduce state size
    public $satuan_data = [];
    public $pembelian_detail_data = [];

    // Modal Kasir
    public $modal_kasir_hari_ini;
    public $show_modal_kasir_form = false;
    public $modal_kasir_jumlah = 0;

    // Computed Properties
    public $subtotal_sebelum_diskon = 0;
    public $total_payment = 0;
    public $sisa_pembayaran = 0;
    public $total_kembalian = 0;

    public $showExtraColumns = false;

    // For preventing rapid updates
    private $lastUpdateTimes = [];
    private $isRestoringSession = false;
    
    // Track item being edited to exclude from stock calculation
    private $editingItemIndex = null;

    // Computed properties to reduce state size with multi-tenancy
    public function getCustomerDataProperty()
    {
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            return [];
        }
        $tokoId = $user->akses->toko_id;

        return DB::table('customer')
            ->where('toko_id', $tokoId)
            ->where('is_opname', false)
            ->select('id', 'nama_customer as name')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function getBarangDataProperty()
    {
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            return [];
        }
        $tokoId = $user->akses->toko_id;

        return DB::table('barang')
            ->where('barang.toko_id', $tokoId)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('jenis_barang.toko_id', $tokoId)
            ->select('barang.id', DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as name"))
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function getGudangDataProperty()
    {
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            return [];
        }
        $tokoId = $user->akses->toko_id;

        return DB::table('gudang')
            ->where('toko_id', $tokoId)
            ->select('id', 'nama_gudang as name')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function mount($id = null)
    {
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }

        $this->tanggal_penjualan = Carbon::now()->format('Y-m-d H:i:s');
        $this->user_id = Auth::id();
        
        // Initialize payment keterangan with a default value
        if (empty($this->payment_keterangan)) {
            $this->payment_keterangan = 'Pembayaran penjualan';
        }
        
        $this->generateNomorPenjualan();
        $this->search();
        if ($id) {
            $this->type = 'edit';
            $this->loadEditData($id);
        } else {
            $this->type = 'create';
            // Load data from session if available (for create mode)
            $this->loadFromSession();
            // Load modal kasir hari ini
            $this->loadModalKasirHariIni();

            // Log mount process untuk debugging
            // Log::info('Component mounted in create mode', [
            //     'user_id' => $this->user_id,
            //     'details_loaded' => count($this->details ?? []),
            //     'payments_loaded' => count($this->payments ?? [])
            // ]);
        }

        // Validate component state after mounting
        $this->validateComponentState();

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Riwayat Penjualan', 'link' => route('penjualan.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }



    private function generateNomorPenjualan()
    {
        $user = Auth::user();
        $tokoId = $user->akses->toko_id;
        
        $today = Carbon::now()->format('Ymd');
        $count = Penjualan::whereDate('created_at', Carbon::today())->count() + 1;
        $this->nomor_penjualan = 'PJ-' . $tokoId . '-' . $today . '-' . sprintf('%03d', $count);
    }

    private function generateNomorPenjualan_final()
    {
        // Get current user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            throw new \Exception('User tidak memiliki akses toko yang valid');
        }
        $tokoId = $user->akses->toko_id;
        
        $today = Carbon::now()->format('Ymd');
        
        // Find the next available number for current toko and date with conflict resolution
        $maxAttempts = 100;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            // Get count of today's transactions for this toko
            $count = DB::table('penjualan')
                      ->where('toko_id', $tokoId)
                      ->whereDate('created_at', Carbon::today())
                      ->count() + 1 + $attempt;
            $nomorPenjualan = 'PJ' .$tokoId. $today . sprintf('%03d', $count);

            // Check if this number already exists for this toko
            $exists = DB::table('penjualan')
                        ->where('toko_id', $tokoId)
                        ->where('nomor_penjualan', $nomorPenjualan)
                        ->exists();
                        
            if (!$exists) {
                return $nomorPenjualan;
            }
            
            $attempt++;
        }
        
        // If we still can't find a unique number, throw exception
        throw new \Exception('Tidak dapat membuat nomor penjualan yang unik setelah ' . $maxAttempts . ' percobaan');
    }

    private function loadEditData($id)
    {
        $penjualan = Penjualan::with(['penjualanDetails', 'pembayaranPenjualan'])->findOrFail($id);
        
        // Validate toko ownership
        if (!$this->validateTokoOwnership($penjualan)) {
            return redirect()->route('penjualan.index');
        }
        
        $this->type = 'edit';
        $this->penjualan_ID = $penjualan->id;
        $this->nomor_penjualan = $penjualan->nomor_penjualan;
        $this->tanggal_penjualan = $penjualan->tanggal_penjualan;
        $this->customer_id = $penjualan->customer_id;
        $this->user_id = Auth::id();
        $this->keterangan = $penjualan->keterangan;
        $this->total_harga = $penjualan->total_harga;
        $this->status = $penjualan->status;
        $this->kembalian = $penjualan->kembalian ?? 0;


        // Load details
        $this->details = $penjualan->penjualanDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'barang_nama' => $detail->barang->nama_barang,
                'satuan_id' => $detail->satuan_id,
                'satuan_nama' => $detail->satuan->nama_satuan,
                'gudang_id' => $detail->gudang_id,
                'gudang_nama' => $detail->gudang->nama_gudang,
                'pembelian_detail_id' => $detail->pembelian_detail_id,
                'harga_satuan' => $detail->harga_satuan,
                'harga_beli' => $detail->pembelianDetail->harga_satuan,
                'jumlah' => $detail->jumlah,
                'nomor_pembelian'   => $detail->pembelianDetail->pembelian->nomor_pembelian,
                'konversi_satuan_terkecil' => $detail->konversi_satuan_terkecil,
                'subtotal' => $detail->subtotal,
                'profit' => $detail->profit,
                'diskon'    => $detail->diskon,
                'biaya_lain'    => $detail->biaya_lain
            ];
        })->toArray();

        // Load payments
        $this->payments = $penjualan->pembayaranPenjualan->map(function ($payment) {
            return [
                'id' => $payment->id,
                'jenis_pembayaran' => $payment->jenis_pembayaran,
                'jumlah' => $payment->jumlah,
                'keterangan' => $payment->keterangan,
            ];
        })->toArray();

        $this->calculateTotals();
    }

    private function saveToSession()
    {
        try {
            if ($this->isRestoringSession) {
                return;
            }
            // Validate that we have data to save
            if (!$this->user_id) {
                Log::error('Cannot save session: user_id is null');
                return;
            }

            $sessionKey = 'penjualan_temp_' . $this->user_id;

            // Sanitize and optimize data before saving to session
            $sessionData = [
                'nomor_penjualan' => $this->nomor_penjualan ?? '',
                'tanggal_penjualan' => $this->tanggal_penjualan ?? Carbon::now()->format('Y-m-d H:i:s'),
                'customer_id' => $this->customer_id,
                'keterangan' => $this->keterangan ?? '',
                'details' => array_values(array_filter($this->details ?? [], function ($detail) {
                    return is_array($detail) && isset($detail['barang_id']);
                })),
                'payments' => array_values(array_filter($this->payments ?? [], function ($payment) {
                    return is_array($payment) && isset($payment['jenis_pembayaran']);
                })),
                'total_harga' => is_numeric($this->total_harga) ? (float) $this->total_harga : 0,
                'status' => $this->status ?? 'belum_bayar',
                'kembalian' => is_numeric($this->kembalian) ? (float) $this->kembalian : 0,
                'detail_form' => $this->getDetailFormState(),
                'subtotal_sebelum_diskon' => is_numeric($this->subtotal_sebelum_diskon) ? (float) $this->subtotal_sebelum_diskon : 0,
                'total_payment' => is_numeric($this->total_payment) ? (float) $this->total_payment : 0,
                'sisa_pembayaran' => is_numeric($this->sisa_pembayaran) ? (float) $this->sisa_pembayaran : 0,
                'total_kembalian' => is_numeric($this->total_kembalian) ? (float) $this->total_kembalian : 0,
            ];

            // Validate session data before saving
            if (empty($sessionData['details']) && !empty($this->details)) {
                Log::warning('Session data validation failed: details array is empty but component has details', [
                    'component_details_count' => count($this->details),
                    'session_details_count' => count($sessionData['details'])
                ]);
                // Force re-add details
                $sessionData['details'] = array_values($this->details);
            }

            session()->put($sessionKey, $sessionData);

            // Verify the session was actually saved
            $verifyData = session()->get($sessionKey);
            if (!$verifyData || !isset($verifyData['details'])) {
                Log::error('Session verification failed after save', [
                    'user_id' => $this->user_id,
                    'session_key' => $sessionKey
                ]);
                // Try to save again
                session()->put($sessionKey, $sessionData);
            }

            // Debug log untuk memastikan data tersimpan
            Log::info('Session saved successfully', [
                'user_id' => $this->user_id,
                'details_count' => count($this->details ?? []),
                'payments_count' => count($this->payments ?? []),
                'session_key' => $sessionKey
            ]);
        } catch (\Exception $e) {
            // If session save fails, log error but don't break the application
            Log::error('Failed to save penjualan session data: ' . $e->getMessage());
        }
    }

    private function getDetailFormState(): array
    {
        return [
            'barang_id' => $this->detail_barang_id,
            'satuan_id' => $this->detail_satuan_id,
            'gudang_id' => $this->detail_gudang_id,
            'pembelian_detail_id' => $this->detail_pembelian_detail_id,
            'harga_satuan' => $this->sanitizeNumeric($this->detail_harga_satuan, 0),
            'harga_beli' => $this->sanitizeNumeric($this->detail_harga_beli, 0),
            'jumlah' => $this->sanitizeNumeric($this->detail_jumlah, 1, 0.01),
            'jumlah_tersedia' => $this->sanitizeNumeric($this->detail_jumlah_tersedia, 0),
            'diskon' => $this->sanitizeNumeric($this->detail_diskon, 0),
            'biaya_lain' => $this->sanitizeNumeric($this->detail_biaya_lain, 0),
        ];
    }

    private function restoreDetailFormState(array $state): void
    {
        $barangId = $state['barang_id'] ?? null;
        if (!$barangId) {
            $this->resetDetailForm();
            return;
        }

        $savedSatuanId = $state['satuan_id'] ?? null;
        $savedGudangId = $state['gudang_id'] ?? null;
        $savedPembelianDetailId = $state['pembelian_detail_id'] ?? null;

        $savedHargaSatuan = $state['harga_satuan'] ?? 0;
        $savedHargaBeli = $state['harga_beli'] ?? 0;
        $savedJumlah = $state['jumlah'] ?? 1;
        $savedDiskon = $state['diskon'] ?? 0;
        $savedBiayaLain = $state['biaya_lain'] ?? 0;
        $savedJumlahTersedia = $state['jumlah_tersedia'] ?? 0;

        $this->detail_barang_id = $barangId;

        if ($savedSatuanId) {
            $this->detail_satuan_id = $savedSatuanId;
        }

        if ($savedGudangId) {
            $this->detail_gudang_id = $savedGudangId;
        }

        $this->updatedDetailBarangId();

        if ($savedSatuanId) {
            $this->detail_satuan_id = $savedSatuanId;
        }

        if ($savedGudangId) {
            $this->detail_gudang_id = $savedGudangId;
            $this->updatedDetailGudangId();
        }

        if ($savedPembelianDetailId) {
            $this->detail_pembelian_detail_id = $savedPembelianDetailId;
            $this->updatedDetailPembelianDetailId();
        } else {
            $this->detail_pembelian_detail_id = null;
        }

        $this->detail_harga_satuan = $savedHargaSatuan;
        $this->detail_harga_beli = $savedHargaBeli;
        $this->detail_jumlah = $savedJumlah;
        $this->detail_diskon = $savedDiskon;
        $this->detail_biaya_lain = $savedBiayaLain;
        $this->detail_jumlah_tersedia = $savedJumlahTersedia;
    }

    private function loadFromSession()
    {
        if ($this->isRestoringSession) {
            return;
        }

        $this->isRestoringSession = true;

        try {
            $sessionKey = 'penjualan_temp_' . $this->user_id;
            $sessionData = session()->get($sessionKey);

            // Debug: Log session loading
            Log::info('loadFromSession called', [
                'user_id' => $this->user_id,
                'session_key' => $sessionKey,
                'session_exists' => !is_null($sessionData),
                'details_count' => isset($sessionData['details']) ? count($sessionData['details']) : 0
            ]);

            if ($sessionData && is_array($sessionData)) {
                // Check if saved nomor_penjualan is still valid (not exists in DB)
                $savedNomor = $sessionData['nomor_penjualan'] ?? null;
                $user = Auth::user();
                $tokoId = $user->akses->toko_id;
                
                if ($savedNomor && DB::table('penjualan')->where('toko_id', $tokoId)->where('nomor_penjualan', $savedNomor)->exists()) {
                    // If nomor already exists, generate new one
                    $this->generateNomorPenjualan();
                } else {
                    $this->nomor_penjualan = $savedNomor ?? $this->nomor_penjualan;
                }
                
                $this->tanggal_penjualan = $sessionData['tanggal_penjualan'] ?? $this->tanggal_penjualan;
                
                // Get default customer for the toko
                $defaultCustomer = DB::table('customer')->where('toko_id', $tokoId)->where('is_opname', false)->first();
                $this->customer_id = $sessionData['customer_id'] ?? ($defaultCustomer ? $defaultCustomer->id : null);

                $this->keterangan = $sessionData['keterangan'] ?? '';

                // Safely load arrays with validation
                $rawDetails = $sessionData['details'] ?? [];
                Log::info('Loading details from session', [
                    'raw_details_count' => is_array($rawDetails) ? count($rawDetails) : 0,
                    'raw_details_type' => gettype($rawDetails)
                ]);
                
                $this->details = is_array($rawDetails) ?
                    array_values(array_filter($rawDetails, function ($detail) {
                        $isValid = is_array($detail) && isset($detail['barang_id']);
                        if (!$isValid) {
                            Log::warning('Invalid detail found in session', ['detail' => $detail]);
                        }
                        return $isValid;
                    })) : [];
                
                Log::info('Details loaded from session', [
                    'loaded_details_count' => count($this->details),
                    'first_detail' => !empty($this->details) ? $this->details[0] : null
                ]);

                $this->payments = is_array($sessionData['payments'] ?? []) ?
                    array_values(array_filter($sessionData['payments'], function ($payment) {
                        return is_array($payment) && isset($payment['jenis_pembayaran']);
                    })) : [];

                $this->total_harga = is_numeric($sessionData['total_harga'] ?? 0) ? (float) $sessionData['total_harga'] : 0;
                $this->status = in_array($sessionData['status'] ?? 'belum_bayar', ['belum_bayar', 'belum_lunas', 'lunas']) ?
                    $sessionData['status'] : 'belum_bayar';
                $this->kembalian = is_numeric($sessionData['kembalian'] ?? 0) ? (float) $sessionData['kembalian'] : 0;

                // Load computed properties
                $this->subtotal_sebelum_diskon = is_numeric($sessionData['subtotal_sebelum_diskon'] ?? 0) ? (float) $sessionData['subtotal_sebelum_diskon'] : 0;
                $this->total_payment = is_numeric($sessionData['total_payment'] ?? 0) ? (float) $sessionData['total_payment'] : 0;
                $this->sisa_pembayaran = is_numeric($sessionData['sisa_pembayaran'] ?? 0) ? (float) $sessionData['sisa_pembayaran'] : 0;
                $this->total_kembalian = is_numeric($sessionData['total_kembalian'] ?? 0) ? (float) $sessionData['total_kembalian'] : 0;

                $detailFormState = $sessionData['detail_form'] ?? [];
                if (is_array($detailFormState)) {
                    $this->restoreDetailFormState($detailFormState);
                } else {
                    $this->resetDetailForm();
                }

                // Debug log untuk memastikan data dimuat
                // Log::info('Session loaded successfully', [
                //     'user_id' => $this->user_id,
                //     'details_count' => count($this->details),
                //     'payments_count' => count($this->payments),
                //     'session_key' => $sessionKey
                // ]);

                $this->calculateTotals();
            } else {
                // Get default customer for the toko
                $user = Auth::user();
                $tokoId = $user->akses->toko_id;
                $defaultCustomer = DB::table('customer')->where('toko_id', $tokoId)->where('is_opname', false)->first();
                $this->customer_id = $defaultCustomer ? $defaultCustomer->id : null;
                $this->resetDetailForm();
                //Log::info('No session data found, using defaults', ['user_id' => $this->user_id]);
            }
        } catch (\Exception $e) {
            // If session loading fails, clear the corrupted session and continue with defaults
            Log::error('Failed to load penjualan session data: ' . $e->getMessage());
            $this->clearSession();
        } finally {
            $this->isRestoringSession = false;
        }
    }

    private function clearSession()
    {
        try {
            $sessionKey = 'penjualan_temp_' . $this->user_id;
            session()->forget($sessionKey);
            //Log::info('Session cleared', ['user_id' => $this->user_id, 'session_key' => $sessionKey]);
        } catch (\Exception $e) {
            Log::error('Failed to clear penjualan session data: ' . $e->getMessage());
        }
    }

    // Method untuk debugging session - bisa dipanggil dari browser console atau testing
    public function debugSession()
    {
        $sessionKey = 'penjualan_temp_' . $this->user_id;
        $sessionData = session()->get($sessionKey);

        // Log::info('Debug Session Data', [
        //     'user_id' => $this->user_id,
        //     'session_key' => $sessionKey,
        //     'session_exists' => !is_null($sessionData),
        //     'session_data' => $sessionData,
        //     'current_details_count' => count($this->details ?? []),
        //     'current_payments_count' => count($this->payments ?? [])
        // ]);

        // $this->info('Debug', 'Session data telah ditulis ke log. Details: ' . count($this->details ?? []) . ', Payments: ' . count($this->payments ?? []));
    }

    // Method untuk test session secara manual
    public function testSession()
    {
        // Simpan data test ke session
        $this->details[] = [
            'id' => null,
            'barang_id' => 999,
            'barang_nama' => 'Test Item',
            'satuan_id' => 1,
            'satuan_nama' => 'PCS',
            'gudang_id' => 1,
            'gudang_nama' => 'Test Gudang',
            'pembelian_detail_id' => 1,
            'nomor_pembelian' => 'TEST001',
            'harga_satuan' => 10000,
            'harga_beli' => 8000,
            'jumlah' => 1,
            'konversi_satuan_terkecil' => 1,
            'subtotal' => 10000,
            'profit' => 2000,
            'diskon' => 0,
            'biaya_lain' => 0,
        ];

        $this->calculateTotals();
        $this->saveToSession();

        $this->success('Test', 'Data test telah ditambahkan dan disimpan ke session. Total items: ' . count($this->details));
    }

    public function updatedDetailBarangId()
    {
        // Get current user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            $this->error('Error', 'User tidak memiliki akses toko yang valid.');
            return;
        }
        $tokoId = $user->akses->toko_id;

        // Reset satuan selection when barang changes
        $this->detail_satuan_id = null;
        
        // Get default gudang for the toko
        $defaultGudang = DB::table('gudang')->where('toko_id', $tokoId)->first();
        $this->detail_gudang_id = $defaultGudang ? $defaultGudang->id : null;
        
        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah_tersedia = 0;

        if ($this->detail_barang_id) {
            // Get satuan terkecil using DB::table with toko_id validation
            $barang = DB::table('barang')
                        ->where('id', $this->detail_barang_id)
                        ->where('toko_id', $tokoId)
                        ->first();
            
            if (!$barang) {
                $this->error('Error', 'Barang tidak valid untuk toko ini.');
                return;
            }
            
            $this->detail_satuan_id = $barang->satuan_terkecil_id;
            
            // Get satuan data using DB::table with toko_id filtering
            $this->satuan_data = DB::table('barang_satuan')
                ->join('satuan', 'barang_satuan.satuan_id', '=', 'satuan.id')
                ->where('barang_satuan.barang_id', $this->detail_barang_id)
                ->where('satuan.toko_id', $tokoId)
                ->select('satuan.id', 'satuan.nama_satuan as name', 'barang_satuan.konversi_satuan_terkecil')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();

            if ($this->detail_barang_id && $this->detail_satuan_id && $this->detail_gudang_id) {
                $this->cekStockGudang();
            }
        } else {
            $this->satuan_data = [];
            $this->detail_satuan_id = NULL;
        }

        // Simpan perubahan ke session
        $this->saveToSession();
    }



    public function updatedDetailSatuanId()
    {
        $this->detail_gudang_id = DB::table('gudang')->select('id')->first()->id;

        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah_tersedia = 0;
        $this->cekStockGudang();

        // Simpan perubahan ke session
        $this->saveToSession();
    }

    private function cekStockGudang()
    {
        // Load available purchase details for this barang, satuan, and gudang
        $barang = DB::table('barang')->join('satuan', 'satuan.id', 'barang.satuan_terkecil_id')->select('barang.nama_barang', 'satuan.nama_satuan')->where('barang.id', $this->detail_barang_id)->first();
        $this->nama_satuan = $barang->nama_satuan;
        
        // Ambil stock aktual dari gudang_stock (sudah memperhitungkan semua transaksi)
        $gudangStock = DB::table('gudang_stock')
            ->where('barang_id', $this->detail_barang_id)
            ->where('gudang_id', $this->detail_gudang_id)
            ->value('jumlah') ?? 0;
        
        // Ambil batch pertama yang masih punya stock (FIFO) untuk harga
        $batch_awal_stock_tersedia = DB::table('pembelian_detail as b')
            ->select(
                'b.id',
                'b.harga_satuan',
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0) as stok_tersedia'),
                'b.rencana_harga_jual',
            )
            ->where('b.barang_id', $this->detail_barang_id)
            ->where('b.gudang_id', $this->detail_gudang_id)
            ->havingRaw('stok_tersedia > 0')
            ->orderBy('b.created_at', 'asc')
            ->first();
            
        // Hitung stock yang sudah digunakan dalam session details (item BARU yang belum tersimpan)
        $stockTerpakai = 0;
        
        if (is_array($this->details) && count($this->details) > 0) {
            foreach ($this->details as $idx => $detail) {
                if (
                    isset($detail['barang_id']) && isset($detail['gudang_id']) &&
                    $detail['barang_id'] == $this->detail_barang_id &&
                    $detail['gudang_id'] == $this->detail_gudang_id
                ) {
                    // Skip item yang sedang diedit
                    if ($this->editingItemIndex !== null && $idx === $this->editingItemIndex) {
                        continue;
                    }
                    
                    // Skip item yang sudah tersimpan di database (sudah dihitung dalam gudang_stock)
                    if (isset($detail['id']) && $detail['id']) {
                        continue;
                    }
                    
                    // Hanya hitung item BARU yang belum tersimpan ke database
                    $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                    $jumlahStock = $detail['jumlah'] * $konversi;
                    $stockTerpakai += $jumlahStock;
                }
            }
        }

        // Stock tersedia = stock di gudang - stock item baru di session
        // gudang_stock sudah memperhitungkan: pembelian, penjualan, retur pembelian, retur penjualan
        $stockTersedia = $gudangStock - $stockTerpakai;

        if ($batch_awal_stock_tersedia && $this->detail_satuan_id && $this->detail_barang_id) {
            // Convert stdClass to array and cast to object for consistent access
            $pembelianDetail = (object)$batch_awal_stock_tersedia;
            $detail_satuan_id = DB::table('barang_satuan')
                ->where('barang_id', $this->detail_barang_id)
                ->where('satuan_id', $this->detail_satuan_id)
                ->first();

            $konversi_satuan_terkecil = $detail_satuan_id->konversi_satuan_terkecil ?? 1;
            // Safely access properties with null coalescing
            $this->detail_harga_beli = round($pembelianDetail->harga_satuan ?? 0, 2);

            // Pastikan stock tersedia tidak negatif
            $stockTersedia = max(0, $stockTersedia);
            // Konversi dari satuan terkecil ke satuan yang dipilih
            // stockTersedia sudah dalam satuan terkecil
            // konversi_satuan_terkecil adalah berapa satuan terkecil per satuan yang dipilih
            // Maka: jumlah_tersedia = stockTersedia / konversi_satuan_terkecil
            $this->detail_jumlah_tersedia = round($stockTersedia / $konversi_satuan_terkecil, 2);

            // Set harga otomatis berdasarkan aturan harga atau batch pembelian
            $this->setHargaOtomatis($pembelianDetail->rencana_harga_jual ?? 0);
        }
    }
    
    /**
     * Menentukan harga jual otomatis berdasarkan:
     * 1. Aturan Harga Barang (jika ada) - berdasarkan jumlah penjualan
     * 2. Harga dari batch pembelian (rencana_harga_jual) - sebagai fallback
     */
    private function setHargaOtomatis($hargaBatchDefault = 0)
    {
        if (!$this->detail_barang_id || !$this->detail_satuan_id) {
            return;
        }
        
        // Gunakan jumlah minimal 1 untuk pengecekan aturan harga
        $jumlah = max(1, $this->detail_jumlah ?? 1);
        
        // Cek apakah ada aturan harga untuk barang dan satuan ini
        $aturanHarga = AturanHargaBarang::where('barang_id', $this->detail_barang_id)
            ->where('satuan_id', $this->detail_satuan_id)
            ->where('minimal_penjualan', '<=', $jumlah)
            ->where(function($query) use ($jumlah) {
                $query->where('maksimal_penjualan', '>=', $jumlah)
                      ->orWhereNull('maksimal_penjualan')
                      ->orWhere('maksimal_penjualan', 0);
            })
            ->orderBy('minimal_penjualan', 'desc') // Ambil aturan dengan minimal tertinggi yang masih cocok
            ->first();
        
        if ($aturanHarga) {
            // Gunakan harga dari aturan harga
            $this->detail_harga_satuan = round($aturanHarga->harga_jual, 2);
            Log::info('Harga dari aturan harga', [
                'barang_id' => $this->detail_barang_id,
                'satuan_id' => $this->detail_satuan_id,
                'jumlah' => $jumlah,
                'harga' => $aturanHarga->harga_jual,
                'aturan_id' => $aturanHarga->id
            ]);
        } else {
            // Fallback ke harga dari batch pembelian
            $this->detail_harga_satuan = round($hargaBatchDefault, 2);
            Log::info('Harga dari batch pembelian (fallback)', [
                'barang_id' => $this->detail_barang_id,
                'satuan_id' => $this->detail_satuan_id,
                'jumlah' => $jumlah,
                'harga' => $hargaBatchDefault
            ]);
        }
    }

    public function updatedDetailGudangId()
    {

        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah_tersedia = 0;

        if ($this->detail_barang_id && $this->detail_satuan_id && $this->detail_gudang_id) {
            // Cek stok gudang setelah gudang dipilih
            $this->cekStockGudang();
            
            // Load available purchase details for this barang, satuan, and gudang
            $barang = DB::table('barang')->join('satuan', 'satuan.id', 'barang.satuan_terkecil_id')->select('barang.nama_barang', 'satuan.nama_satuan')->where('barang.id', $this->detail_barang_id)->first();
            $nama_satuan = $barang->nama_satuan;
            $pembelianDetailRaw = DB::table('pembelian_detail as b')
                ->select(
                    'b.id',
                    'b.harga_satuan',
                    DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                    DB::raw('(b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0) as stok_tersedia'),
                    'b.rencana_harga_jual'
                )
                ->where('b.barang_id', $this->detail_barang_id)
                //->where('b.satuan_id', $this->detail_satuan_id)
                ->where('b.gudang_id', $this->detail_gudang_id)
                ->havingRaw('stok_tersedia > 0')
                ->get();

            // Adjust stock tersedia by subtracting stock already used in session
            $this->pembelian_detail_data = $pembelianDetailRaw->map(function ($item) use ($nama_satuan) {
                $stockTerpakai = 0;
                $stockDikembalikan = 0;
                
                if (is_array($this->details) && count($this->details) > 0) {
                    foreach ($this->details as $idx => $detail) {
                        if (
                            isset($detail['pembelian_detail_id']) &&
                            $detail['pembelian_detail_id'] == $item->id
                        ) {
                            $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                            $jumlahStock = $detail['jumlah'] * $konversi;
                            
                            // Jika item sedang diedit DAN sudah tersimpan di database
                            // Stock-nya harus dikembalikan karena sudah dikurangi dalam query SQL
                            if ($this->editingItemIndex !== null && $idx === $this->editingItemIndex && isset($detail['id']) && $detail['id']) {
                                $stockDikembalikan += $jumlahStock;
                                continue;
                            }
                            
                            // Skip item yang sedang diedit tapi belum tersimpan (item baru)
                            if ($this->editingItemIndex !== null && $idx === $this->editingItemIndex) {
                                continue;
                            }
                            
                            // Skip item yang sudah tersimpan di database (sudah dihitung dalam query SQL)
                            if (isset($detail['id']) && $detail['id']) {
                                continue;
                            }
                            
                            // Hanya hitung item BARU yang belum tersimpan ke database
                            $stockTerpakai += $jumlahStock;
                        }
                    }
                }

                // Stock tersedia = stock dari database + stock dikembalikan - stock item baru di session
                $adjustedStock = max(0, $item->stok_tersedia + $stockDikembalikan - $stockTerpakai);

                return [
                    'id' => $item->id,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah_beli' => $item->jumlah_beli,
                    'stok_tersedia' => $adjustedStock,
                    'rencana_harga_jual' => $item->rencana_harga_jual,
                    'name' => "Rp " . number_format($item->harga_satuan, 0, ',', '.') . " - Stok $nama_satuan: " . number_format($adjustedStock, 2, ',', '.')
                ];
            })->filter(function ($item) {
                // Only keep items with available stock
                return $item['stok_tersedia'] > 0;
            })->values()->toArray();
        }
    }

    public function search(string $value = '')
    {
        // Get current user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            $this->getBarangSearch = [];
            return;
        }
        $tokoId = $user->akses->toko_id;

        // Get selected barang if exists - using DB::table with toko_id filtering
        $selectedBarang = DB::table('barang')
            ->where('barang.id', $this->detail_barang_id)
            ->where('barang.toko_id', $tokoId)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('jenis_barang.toko_id', $tokoId)
            ->select(
                'barang.id',
                DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as name"),
                'barang.kode_barang'
            )
            ->get()
            ->map(function ($item) {
                return (array) $item;
            });

        // Search barang based on name, kode_barang, or jenis_barang - using DB::table with toko_id filtering
        $searchResults = DB::table('barang')
            ->where('barang.toko_id', $tokoId)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('jenis_barang.toko_id', $tokoId)
            ->where(function ($query) use ($value) {
                $query->where('barang.kode_barang', 'like', "%$value%")
                    ->orWhere('barang.nama_barang', 'like', "%$value%")
                    ->orWhere('jenis_barang.nama_jenis_barang', 'like', "%$value%");
            })
            ->select(
                'barang.id',
                DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as name"),
                'barang.kode_barang'
            )
            ->take(10) // Increased from 5 to 10 for better search results
            ->orderBy('barang.nama_barang')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->merge($selectedBarang);

        $this->getBarangSearch = $searchResults->toArray();
    }

    /**
     * Handle barcode search and auto-add item
     */
    public function updatedBarcodeSearch()
    {
        if (empty($this->barcode_search)) {
            return;
        }

        // Get current user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            $this->warning('Error', 'User tidak memiliki akses toko yang valid.');
            return;
        }
        $tokoId = $user->akses->toko_id;

        // Search for barang by kode_barang (barcode) - exact match using DB::table with toko_id filtering
        $barang = DB::table('barang')
            ->where('barang.toko_id', $tokoId)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('jenis_barang.toko_id', $tokoId)
            ->where('barang.kode_barang', trim($this->barcode_search))
            ->select(
                'barang.id',
                'barang.nama_barang',
                'barang.kode_barang',
                'barang.satuan_terkecil_id',
                'jenis_barang.nama_jenis_barang'
            )
            ->first();

        // If exact match not found, try case-insensitive search
        if (!$barang) {
            $barang = DB::table('barang')
                ->where('barang.toko_id', $tokoId)
                ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->where('jenis_barang.toko_id', $tokoId)
                ->whereRaw('LOWER(barang.kode_barang) = LOWER(?)', [trim($this->barcode_search)])
                ->select(
                    'barang.id',
                    'barang.nama_barang',
                    'barang.kode_barang',
                    'barang.satuan_terkecil_id',
                    'jenis_barang.nama_jenis_barang'
                )
                ->first();
        }

        if (!$barang) {
            $this->warning('Peringatan', 'Barang dengan kode barcode "' . $this->barcode_search . '" tidak ditemukan.');
            $this->barcode_search = '';
            return;
        }

        // Auto-fill form with found item
        $this->detail_barang_id = $barang->id;
        $this->updatedDetailBarangId();

        // Set default satuan to satuan terkecil
        if ($barang->satuan_terkecil_id && !empty($this->satuan_data)) {
            $satuanTerkecil = collect($this->satuan_data)->firstWhere('id', $barang->satuan_terkecil_id);
            if ($satuanTerkecil) {
                $this->detail_satuan_id = $barang->satuan_terkecil_id;
                $this->updatedDetailSatuanId();
            }
        }

        $rencana_harga_jual = $this->detail_harga_satuan;
        // Set default gudang if only one available
        if (count($this->gudang_data) === 1) {
            $this->detail_gudang_id = $this->gudang_data[0]['id'];
            $this->updatedDetailGudangId();
        }

        if ($this->detail_harga_satuan == 0) {
            $this->updatedDetailBarangId();
            $this->detail_harga_satuan = $rencana_harga_jual;
        }

        // Set default quantity to 1
        $this->detail_jumlah = 1;

        // Clear barcode search
        $this->barcode_search = '';

        // Show success message
        $this->success('Berhasil', 'Barang "' . $barang->nama_barang . '" berhasil ditemukan dan ditambahkan ke form.');

        // Auto-add if all required fields are filled
        if ($this->detail_barang_id && $this->detail_satuan_id && $this->detail_gudang_id && $this->detail_harga_satuan > 0) {
            try {
                $this->addDetail();
                // Show success message with item name
                $this->success('Berhasil', 'Item "' . $barang->nama_barang . '" berhasil ditambahkan ke penjualan!');
                // Dispatch event to refocus barcode input
                $this->dispatch('refocus-barcode');
            } catch (\Exception $e) {
                // If auto-add fails, show error message
                $this->error('Error', 'Gagal menambahkan item: ' . $e->getMessage());
            }
        } else {
            // If not all fields are filled, show info about what's missing
            $missing = [];
            if (!$this->detail_satuan_id) $missing[] = 'satuan';
            if (!$this->detail_gudang_id) $missing[] = 'gudang';
            if (!($this->detail_harga_satuan > 0)) $missing[] = 'harga';

            if (!empty($missing)) {
                $this->info('Info', 'Item ditemukan! Silakan lengkapi: ' . implode(', ', $missing) . ' kemudian tekan Ctrl + F2 untuk menambah.');
            }
        }
    }

    /**
     * Quick add item by barcode (alternative method for direct scanning)
     */
    public function quickAddByBarcode($barcode)
    {
        $this->barcode_search = $barcode;
        $this->updatedBarcodeSearch();
    }

    /**
     * Clear barcode input and reset detail form
     */
    public function clearBarcodeForm()
    {
        $this->barcode_search = '';
        $this->detail_barang_id = null;
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = null;
        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah = 1;
        $this->detail_jumlah_tersedia = 0;
        $this->detail_diskon = 0;
        $this->detail_biaya_lain = 0;
        $this->satuan_data = [];
        $this->pembelian_detail_data = [];

        $this->saveToSession();

        // Dispatch event to refocus barcode input
        $this->dispatch('refocus-barcode');
    }

    public function updatedDetailPembelianDetailId()
    {
        if ($this->detail_pembelian_detail_id) {

            $pembelianDetail = collect($this->pembelian_detail_data)
                ->firstWhere('id', $this->detail_pembelian_detail_id);

            if ($pembelianDetail && $this->detail_satuan_id && $this->detail_barang_id) {
                $detail_satuan_id = DB::table('barang_satuan')
                    ->where('barang_id', $this->detail_barang_id)
                    ->where('satuan_id', $this->detail_satuan_id)
                    ->first();
                $konversi_satuan_terkecil = $detail_satuan_id->konversi_satuan_terkecil ?? 1;

                $this->detail_harga_beli = round($pembelianDetail['harga_satuan'], 2);

                // Hitung stock yang sudah digunakan dalam session details untuk pembelian detail ini
                $stockTerpakai = 0;
                $stockDikembalikan = 0; // Stock dari item yang sedang diedit (untuk dikembalikan)
                
                if (is_array($this->details) && count($this->details) > 0) {
                    foreach ($this->details as $idx => $detail) {
                        if (
                            isset($detail['pembelian_detail_id']) &&
                            $detail['pembelian_detail_id'] == $this->detail_pembelian_detail_id
                        ) {
                            $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                            $jumlahStock = $detail['jumlah'] * $konversi;
                            
                            // Jika ini adalah item yang sedang diedit dan punya id (sudah tersimpan di database)
                            if ($this->editingItemIndex !== null && $idx === $this->editingItemIndex && isset($detail['id'])) {
                                // Kembalikan stock lama (karena akan diganti dengan yang baru)
                                $stockDikembalikan += $jumlahStock;
                            }
                            // Jika item sedang diedit tapi belum punya id (item baru), skip
                            elseif ($this->editingItemIndex !== null && $idx === $this->editingItemIndex) {
                                continue;
                            }
                            // Pada mode edit, skip item yang sudah tersimpan di database (kecuali yang sedang diedit)
                            elseif ($this->type === 'edit' && isset($detail['id'])) {
                                continue;
                            }
                            // Item lainnya, hitung sebagai stock terpakai
                            else {
                                $stockTerpakai += $jumlahStock;
                            }
                        }
                    }
                }

                // Set harga otomatis berdasarkan aturan harga atau batch pembelian yang dipilih
                $rencana_harga_jual = round($pembelianDetail['rencana_harga_jual'] ?? 0, 2);
                $this->setHargaOtomatis($rencana_harga_jual);
            }
        }
    }

    public function addDetail()
    {
        $this->validate([
            'detail_barang_id' => 'required',
            'detail_satuan_id' => 'required',
            'detail_gudang_id' => 'required',
            'detail_harga_satuan' => 'required|numeric|min:0',
            'detail_jumlah' => 'required|numeric|min:0.1|max:' . $this->detail_jumlah_tersedia,
        ]);

        // Get barang, satuan, gudang info
        $barang = Barang::find($this->detail_barang_id);
        $satuan = collect($this->satuan_data)->firstWhere('id', $this->detail_satuan_id);
        $gudang = collect($this->gudang_data)->firstWhere('id', $this->detail_gudang_id);
        // Get available batches ordered by FIFO
        $availableBatches = DB::table('pembelian_detail as b')
            ->select(
                'b.id',
                'b.harga_satuan',
                'b.pembelian_id',
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                DB::raw('ROUND((b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0), 2) as stok_tersedia'),
                'b.rencana_harga_jual'
            )
            ->where('b.barang_id', $this->detail_barang_id)
            ->where('b.gudang_id', $this->detail_gudang_id)
            ->havingRaw('stok_tersedia > 0')
            ->orderBy('b.created_at', 'asc')
            ->get();

        // Adjust available batches by subtracting stock already used in session
        $availableBatches = $availableBatches->map(function ($batch) {
            $stockTerpakai = 0;
            if (is_array($this->details) && count($this->details) > 0) {
                foreach ($this->details as $detail) {
                    if (
                        isset($detail['pembelian_detail_id']) &&
                        $detail['pembelian_detail_id'] == $batch->id
                    ) {
                        // Konversi ke satuan terkecil untuk perhitungan yang akurat
                        $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                        $stockTerpakai += $detail['jumlah'] * $konversi;
                    }
                }
            }

            // Adjust stock tersedia
            $batch->stok_tersedia = max(0, $batch->stok_tersedia - $stockTerpakai);
            return $batch;
        })->filter(function ($batch) {
            // Only keep batches with available stock
            return $batch->stok_tersedia > 0;
        });

        if (!$barang || !$satuan || !$gudang) {
            $this->error('Error', 'Data barang, satuan, atau gudang tidak valid.');
            return;
        }

        // Calculate how many units we need to fulfill from each batch
        $remainingQuantity = $this->detail_jumlah * $satuan['konversi_satuan_terkecil'];
        $batchDetails = [];

        foreach ($availableBatches as $batch) {
            if ($remainingQuantity <= 0) break;

            $quantityFromBatch = min($batch->stok_tersedia, $remainingQuantity);

            $batchDetails[] = [
                'pembelian_detail_id' => $batch->id,
                'nomor_pembelian'  => DB::table('pembelian')->where('id', $batch->pembelian_id)->value('nomor_pembelian'),
                'jumlah' => $quantityFromBatch / $satuan['konversi_satuan_terkecil'],
                'harga_beli' => $batch->harga_satuan,
                'harga_jual' => $this->detail_harga_satuan
            ];

            $remainingQuantity -= $quantityFromBatch;
        }

        if ($remainingQuantity > 0) {
            $this->error('Error', 'Stok tidak mencukupi untuk memenuhi pesanan.');
            return;
        }

        // Add details for each batch used, merging with existing rows when possible
        $requestedQuantity = max($this->detail_jumlah, 0.0001);

        foreach ($batchDetails as $batchDetail) {
            $quantity = $batchDetail['jumlah'];
            $subtotal = $batchDetail['harga_jual'] * $quantity;
            $diskonPortion = ($this->detail_diskon * $quantity) / $requestedQuantity;
            $biayaPortion = ($this->detail_biaya_lain * $quantity) / $requestedQuantity;
            $profitBase = ($batchDetail['harga_jual'] - $batchDetail['harga_beli']) * $quantity;
            $profit = $profitBase - $diskonPortion + $biayaPortion;

            $existingIndex = collect($this->details)->search(function ($detail) use ($batchDetail) {
                return isset($detail['barang_id'], $detail['gudang_id'], $detail['satuan_id'], $detail['pembelian_detail_id'])
                    && $detail['barang_id'] === $this->detail_barang_id
                    && $detail['satuan_id'] === $this->detail_satuan_id
                    && $detail['gudang_id'] === $this->detail_gudang_id
                    && $detail['pembelian_detail_id'] === $batchDetail['pembelian_detail_id'];
            });

            if ($existingIndex !== false) {
                $this->details[$existingIndex]['jumlah'] += $quantity;
                $this->details[$existingIndex]['subtotal'] += $subtotal;
                $this->details[$existingIndex]['profit'] += $profit;
                $this->details[$existingIndex]['diskon'] = ($this->details[$existingIndex]['diskon'] ?? 0) + $diskonPortion;
                $this->details[$existingIndex]['biaya_lain'] = ($this->details[$existingIndex]['biaya_lain'] ?? 0) + $biayaPortion;
                $this->details[$existingIndex]['harga_satuan'] = $batchDetail['harga_jual'];
                $this->details[$existingIndex]['harga_beli'] = $batchDetail['harga_beli'];
            } else {
                $this->details[] = [
                    'id' => null,
                    'barang_id' => $this->detail_barang_id,
                    'barang_nama' => $barang->nama_barang,
                    'satuan_id' => $this->detail_satuan_id,
                    'satuan_nama' => $satuan['name'],
                    'gudang_id' => $this->detail_gudang_id,
                    'gudang_nama' => $gudang['name'],
                    'pembelian_detail_id' => $batchDetail['pembelian_detail_id'],
                    'nomor_pembelian'   => $batchDetail['nomor_pembelian'],
                    'harga_satuan' => $batchDetail['harga_jual'],
                    'harga_beli' => $batchDetail['harga_beli'],
                    'jumlah' => $quantity,
                    'konversi_satuan_terkecil' => $satuan['konversi_satuan_terkecil'],
                    'subtotal' => $subtotal,
                    'profit' => $profit,
                    'diskon' => $diskonPortion,
                    'biaya_lain' => $biayaPortion,
                ];
            }
        }

        $this->resetDetailForm();
        $this->calculateTotals();
        $this->saveToSession();

        // Debug log untuk memastikan addDetail berhasil
        Log::info('Item added to details', [
            'user_id' => $this->user_id,
            'barang_id' => $barang->id,
            'barang_nama' => $barang->nama_barang,
            'total_details' => count($this->details),
            'last_detail' => end($this->details)
        ]);

        $this->success('Berhasil', 'Item berhasil ditambahkan.');
        $this->resetValidation();

        // Fokus kembali ke input barcode setelah item ditambahkan
        $this->dispatch('refocus-barcode');
    }

    public function removeDetail($index)
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->calculateTotals();
        $this->saveToSession();
        $this->success('Berhasil', 'Item berhasil dihapus.');
    }

    /**
     * Increase quantity by 1
     */
    public function increaseQuantity()
    {
        if ($this->detail_jumlah < $this->detail_jumlah_tersedia) {
            $this->detail_jumlah = min($this->detail_jumlah + 1, $this->detail_jumlah_tersedia);
            $this->updateHargaByJumlah();
            $this->calculateTotals();
        }
    }

    /**
     * Decrease quantity by 1
     */
    public function decreaseQuantity()
    {
        if ($this->detail_jumlah > 0) {
            $this->detail_jumlah = max($this->detail_jumlah - 1, 0);
            $this->updateHargaByJumlah();
            $this->calculateTotals();
        }
    }

        public function editDetail($index)
    {
        if (!isset($this->details[$index])) {
            return;
        }

        $detail = $this->details[$index];
        
        // Set index yang sedang diedit untuk mengecualikan dari perhitungan stok
        $this->editingItemIndex = $index;

        // Preload dropdown and stock data
        $this->detail_barang_id = $detail['barang_id'];
        $this->detail_satuan_id = $detail['satuan_id'];
        $this->detail_gudang_id = $detail['gudang_id'];

        $this->updatedDetailBarangId();
        $this->updatedDetailGudangId();

        if (!empty($detail['pembelian_detail_id'])) {
            $this->detail_pembelian_detail_id = $detail['pembelian_detail_id'];
            $this->updatedDetailPembelianDetailId();
        }

        // Restore original values after helper methods run
        $this->detail_harga_satuan = $detail['harga_satuan'];
        $this->detail_harga_beli = $detail['harga_beli'];
        $this->detail_jumlah = $detail['jumlah'];
        $this->detail_diskon = $detail['diskon'] ?? 0;
        $this->detail_biaya_lain = $detail['biaya_lain'] ?? 0;

        // Remove the item from the list so it can be edited
        $this->removeDetail($index);
        
        // Reset editing index setelah item dihapus
        $this->editingItemIndex = null;

        $this->info('Info', 'Item berhasil dimuat ke form untuk diedit. Silakan ubah data dan klik tambah untuk menyimpan perubahan.');
    }

    public function updateDetailQuantity($index, $newQuantity)
    {
        // Prevent rapid updates that can cause session conflicts
        if ($this->isRapidUpdate('updateDetailQuantity_' . $index)) {
            return;
        }

        // Verify data integrity and attempt recovery if needed
        if (!$this->verifyDataIntegrity('updateDetailQuantity_start')) {
            Log::error('Data integrity verification failed in updateDetailQuantity', [
                'user_id' => $this->user_id,
                'index' => $index,
                'details_count' => count($this->details ?? [])
            ]);
            $this->error('Error', 'Data detail penjualan tidak ditemukan. Silakan refresh halaman.');
            return;
        }

        // Debug: Log the update attempt
        Log::info('updateDetailQuantity called', [
            'index' => $index,
            'newQuantity' => $newQuantity,
            'current_details_count' => count($this->details ?? []),
            'user_id' => $this->user_id
        ]);

        // Validate index after potential session reload
        if (!isset($this->details[$index])) {
            Log::error('Invalid index in updateDetailQuantity', [
                'index' => $index,
                'details_count' => count($this->details ?? []),
                'available_indexes' => array_keys($this->details ?? [])
            ]);
            $this->error('Error', 'Item tidak ditemukan. Index: ' . $index);
            return;
        }

        if (isset($this->details[$index])) {
            // Validate quantity
            if (!is_numeric($newQuantity) || $newQuantity <= 0) {
                $this->error('Error', 'Jumlah harus berupa angka positif.');
                return;
            }

            $detail = &$this->details[$index];

            // Get available stock for this pembelian_detail_id
            $pembelianDetail = DB::table('pembelian_detail as b')
                ->select(
                    'b.id',
                    'b.harga_satuan',
                    DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                    DB::raw('ROUND((b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0), 2) as stok_tersedia')
                )
                ->where('b.id', $detail['pembelian_detail_id'])
                ->first();

            if (!$pembelianDetail) {
                $this->error('Error', 'Data pembelian tidak ditemukan.');
                return;
            }

            // Calculate stock already used in current session (excluding current item)
            $stockTerpakai = 0;
            foreach ($this->details as $idx => $sessionDetail) {
                if (
                    $idx !== $index &&
                    isset($sessionDetail['pembelian_detail_id']) &&
                    $sessionDetail['pembelian_detail_id'] == $detail['pembelian_detail_id']
                ) {
                    $konversi = $sessionDetail['konversi_satuan_terkecil'] ?? 1;
                    $stockTerpakai += $sessionDetail['jumlah'] * $konversi;
                }
            }

            // Calculate available stock
            $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
            $stockTersediaAdjusted = ($pembelianDetail->stok_tersedia - $stockTerpakai) / $konversi;

            // Check if new quantity exceeds available stock
            if ($newQuantity > $stockTersediaAdjusted) {
                $this->error('Error', 'Jumlah melebihi stok tersedia. Stok tersedia: ' . number_format($stockTersediaAdjusted, 0));
                return;
            }

            // Update quantity and recalculate
            $detail['jumlah'] = (float) $newQuantity;

            // Recalculate subtotal and profit
            $harga_setelah_diskon = $detail['harga_satuan'] - ($detail['diskon'] ?? 0);
            $harga_dengan_biaya = $harga_setelah_diskon + ($detail['biaya_lain'] ?? 0);
            $detail['subtotal'] = $detail['jumlah'] * $harga_dengan_biaya;
            $detail['profit'] = ($harga_setelah_diskon - $detail['harga_beli']) * $detail['jumlah'];

            // Recalculate totals
            $this->calculateTotals();

            // Save to session with validation
            $this->saveToSession();

            // Verify session was saved successfully and details are intact
            $sessionKey = 'penjualan_temp_' . $this->user_id;
            $sessionData = session()->get($sessionKey);
            $currentDetailsCount = count($this->details ?? []);
            
            if (!$sessionData || !isset($sessionData['details']) || count($sessionData['details']) !== $currentDetailsCount) {
                Log::error('Session data mismatch after updateDetailQuantity', [
                    'user_id' => $this->user_id,
                    'index' => $index,
                    'current_details_count' => $currentDetailsCount,
                    'session_details_count' => isset($sessionData['details']) ? count($sessionData['details']) : 0,
                    'session_exists' => !is_null($sessionData)
                ]);
                
                // Force save with retry mechanism
                for ($retry = 0; $retry < 3; $retry++) {
                    $this->saveToSession();
                    $verifySession = session()->get($sessionKey);
                    if ($verifySession && isset($verifySession['details']) && count($verifySession['details']) === $currentDetailsCount) {
                        Log::info('Session recovery successful on retry ' . ($retry + 1));
                        break;
                    }
                    if ($retry === 2) {
                        Log::error('Failed to save session after 3 retries');
                        $this->error('Error', 'Gagal menyimpan data. Silakan coba lagi.');
                        return;
                    }
                }
            }
            
            // Final data integrity verification
            if (!$this->verifyDataIntegrity('updateDetailQuantity_end')) {
                Log::error('Data integrity verification failed at end of updateDetailQuantity');
                $this->error('Error', 'Data hilang setelah update. Silakan refresh halaman.');
                return;
            }

            $this->success('Berhasil', 'Jumlah item berhasil diupdate.');
            // dd($sessionData);
        }
    }

    public function handleShortcutAddItem(): void
    {
        // Shortcut-triggered add item mirrors the standard addDetail workflow.
        $this->addDetail();
    }

    public function handleShortcutSelectPaymentType(array $payload = []): void
    {
        // Normalize shortcut payload before mutating payment state.
        $type = $payload['type'] ?? null;
        $allowed = ['cash', 'transfer', 'check', 'other'];
        if (!$type || !in_array($type, $allowed, true)) {
            return;
        }

        $this->payment_jenis = $type;

        if (empty($this->payment_keterangan)) {
            $this->payment_keterangan = 'Pembayaran penjualan ' . ($this->nomor_penjualan ?? '');
        }

        $this->saveToSession();
    }

    public function handleShortcutFillFullPayment(): void
    {
        // Reuse existing logic to populate outstanding balance.
        $this->quickFillPayment();
    }

    public function handleShortcutSetQuickCash(array $payload = []): void
    {
        // Quickly inject predefined cash denomination into the amount field.
        $amount = $payload['amount'] ?? null;
        if (!is_numeric($amount)) {
            return;
        }

        $this->payment_jenis = 'cash';
        $this->payment_jumlah = (float) $amount;

        if (empty($this->payment_keterangan)) {
            $this->payment_keterangan = 'Pembayaran penjualan ' . ($this->nomor_penjualan ?? '');
        }

        $this->saveToSession();
    }

    public function handleShortcutSetSuggestedPayment(array $payload = []): void
    {
        // Apply the indexed suggested payment amount when provided.
        $amount = $payload['amount'] ?? null;
        if (!is_numeric($amount)) {
            return;
        }

        $this->payment_jumlah = (float) $amount;

        if (empty($this->payment_keterangan)) {
            $this->payment_keterangan = 'Pembayaran penjualan ' . ($this->nomor_penjualan ?? '');
        }

        $this->saveToSession();
    }

    public function handleShortcutAddPayment()
    {
        // Mirrors the UI add payment button for keyboard users.
        $this->addPayment();
    }

    public function handleShortcutSaveTransaction()
    {
        // Persist transaction depending on current form mode.
        if ($this->type === 'edit') {
            return $this->update();
        }

        return $this->store();
    }

    public function handleShortcutPayAndSave()
    {
        // Finalize payments and persist based on form mode.
        if ($this->type === 'edit') {
            return $this->addPaymentAndUpdate();
        }

        return $this->addPaymentAndSave();
    }

    public function handleShortcutAutoSettlePrintRedirect()
    {
        // Automate settlement before printing and preparing a new sale.
        $this->calculateTotals();

        if ($this->sisa_pembayaran > 0 && $this->payment_jumlah <= 0) {
            $this->payment_jenis = 'cash';
            $this->payment_jumlah = $this->sisa_pembayaran;

            if (empty($this->payment_keterangan)) {
                $this->payment_keterangan = 'Pembayaran penjualan ' . ($this->nomor_penjualan ?? '');
            }
        }

        $this->postShortcutIntent = 'print-payment';
        session()->flash('pos_redirect_after_print', route('penjualan.create'));

        return $this->type === 'edit'
            ? $this->addPaymentAndUpdate()
            : $this->addPaymentAndSave();
    }

    public function addPayment()
    {
        $this->validate([
            'payment_jenis' => 'required',
            'payment_jumlah' => 'required|numeric|min:1',
            'payment_keterangan' => 'required',
        ]);

        // Check if payment would exceed total
        $new_total_payment = $this->total_payment + $this->payment_jumlah;
        // if ($new_total_payment > $this->total_harga) {
        //     $this->warning('Peringatan', 'Jumlah pembayaran melebihi total yang harus dibayar. Sisa yang perlu dibayar: Rp ' . number_format($this->sisa_pembayaran, 0, ',', '.'));
        //     return;
        // }

        // Calculate kembalian for this payment
        $roundedTotal = round($this->total_harga / 100) * 100;
        $currentTotalPayment = collect($this->payments)->sum('jumlah') + $this->payment_jumlah;
        $kembalian = max(0, $currentTotalPayment - $roundedTotal);

        // Save payment to database immediately if editing
        if ($this->type === 'edit' && $this->penjualan_ID) {
            $payment = PembayaranPenjualan::create([
                'penjualan_id' => $this->penjualan_ID,
                'user_id' => $this->user_id,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
            ]);

            // Add to local array with database ID
            $this->payments[] = [
                'id' => $payment->id,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
                'kembalian' => $kembalian,
            ];

            // Update penjualan status
            Penjualan::where('id', $this->penjualan_ID)->update([
                'status' => $this->status,
                'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
            ]);
        } else {
            // Just add to local array for create mode
            $this->payments[] = [
                'id' => null,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
                'kembalian' => $kembalian,
            ];
        }

        $this->resetPaymentForm();
        $this->calculateTotals();
        $this->saveToSession();

        if ($this->status === 'lunas') {
            $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Penjualan telah LUNAS!');
        } else {
            $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Total: ' . count($this->payments) . ' pembayaran.');
        }

        $this->resetValidation();
    }

    public function removePayment($index)
    {
        // Delete the payment record from database if it has an ID
        if (isset($this->payments[$index]['id']) && $this->payments[$index]['id']) {
            $paymentId = $this->payments[$index]['id'];
            PembayaranPenjualan::where('id', $paymentId)->delete();

            // If in edit mode, update the transaction status too
            if ($this->type === 'edit' && $this->penjualan_ID) {
                // Remove from local array
                unset($this->payments[$index]);
                $this->payments = array_values($this->payments);
                $this->calculateTotals();

                // Update penjualan status
                Penjualan::where('id', $this->penjualan_ID)->update([
                    'status' => $this->status,
                    'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
                ]);
            }
        } else {
            // Just remove from local array if no ID (not yet saved to database)
            unset($this->payments[$index]);
            $this->payments = array_values($this->payments);
            $this->calculateTotals();
        }

        $this->saveToSession();
        $this->success('Berhasil', 'Pembayaran berhasil dihapus.');
    }

    private function resetDetailForm()
    {
        $this->detail_barang_id = null;
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = null;
        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah = 1;
        $this->detail_jumlah_tersedia = 0;
        $this->detail_diskon = 0;
        $this->detail_biaya_lain = 0;
        $this->satuan_data = [];
        $this->pembelian_detail_data = [];

        $this->saveToSession();
    }

    private function resetPaymentForm()
    {
        $this->payment_jenis = 'cash';
        $this->payment_jumlah = 0;
        $this->payment_keterangan = '';
    }

    public function calculateTotals()
    {
        try {
            // Ensure arrays are valid before processing
            $details = is_array($this->details) ? $this->details : [];
            $payments = is_array($this->payments) ? $this->payments : [];

            // Calculate subtotal, total diskon, dan total biaya lain dari detail items
            $this->subtotal_sebelum_diskon = 0;
            $total_diskon = 0;
            $total_biaya_lain = 0;

            foreach ($details as $detail) {
                if (!is_array($detail)) continue;

                $subtotal = $this->sanitizeNumeric($detail['subtotal'] ?? 0, 0);
                $diskon = $this->sanitizeNumeric($detail['diskon'] ?? 0, 0);
                $biaya_lain = $this->sanitizeNumeric($detail['biaya_lain'] ?? 0, 0);

                $this->subtotal_sebelum_diskon += $subtotal;
                $total_diskon += $diskon;
                $total_biaya_lain += $biaya_lain;
            }

            // Calculate total harga: subtotal - total diskon + total biaya lain
            $this->total_harga = max(0, $this->subtotal_sebelum_diskon - $total_diskon + $total_biaya_lain);

            // Calculate total payment with enhanced validation
            $this->total_payment = collect($payments)->sum(function ($payment) {
                if (!is_array($payment) || !isset($payment['jumlah'])) {
                    return 0;
                }
                return $this->sanitizeNumeric($payment['jumlah'], 0);
            });

            // Round total_harga to nearest hundred before calculating remaining payment and kembalian
            $roundedTotal = round($this->total_harga / 100) * 100;
            $this->sisa_pembayaran = (float)$roundedTotal - (float)$this->total_payment;

            // Calculate total kembalian (only if payment exceeds total)
            $this->total_kembalian = max(0, $this->total_payment - $roundedTotal);

            // Auto-calculate status based on payment
            $this->updatePaymentStatus();

            // Dispatch browser event to update UI if needed
            $this->dispatch('totals-updated', [
                'subtotal' => $this->subtotal_sebelum_diskon,
                'total' => $this->total_harga,
                'payment' => $this->total_payment,
                'remaining' => $this->sisa_pembayaran
            ]);
        } catch (\Exception $e) {
            Log::warning('Error calculating totals: ' . $e->getMessage());
            // Set safe defaults
            $this->subtotal_sebelum_diskon = 0;
            $this->total_harga = 0;
            $this->total_payment = 0;
            $this->sisa_pembayaran = 0;
            $this->status = 'belum_bayar';
        }
    }

    private function updatePaymentStatus()
    {
        if ($this->total_payment == 0) {
            $this->status = 'belum_bayar';
        } elseif ($this->total_payment < round($this->total_harga / 100) * 100) {
            $this->status = 'belum_lunas';
        } else {
            $this->status = 'lunas';
        }
    }



    public function updatedDetailHargaSatuan($value)
    {
        try {
            $this->detail_harga_satuan = $this->sanitizeNumeric($value, 0);
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::warning('Error updating detail_harga_satuan: ' . $e->getMessage());
            $this->detail_harga_satuan = 0;
        }
    }

    public function updatedDetailJumlah($value)
    {
        try {
            $this->detail_jumlah = $this->sanitizeNumeric($value, 1, 0.01);
            
            // Update harga otomatis berdasarkan jumlah (untuk aturan harga bertingkat)
            $this->updateHargaByJumlah();
            
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::warning('Error updating detail_jumlah: ' . $e->getMessage());
            $this->detail_jumlah = 1;
        }
    }
    
    /**
     * Update harga saat jumlah berubah (untuk aturan harga bertingkat)
     */
    private function updateHargaByJumlah()
    {
        if (!$this->detail_barang_id || !$this->detail_satuan_id || !$this->detail_gudang_id) {
            return;
        }
        
        $hargaBatchDefault = 0;
        
        // Jika ada batch yang dipilih, gunakan harga dari batch tersebut
        if ($this->detail_pembelian_detail_id && !empty($this->pembelian_detail_data)) {
            $selectedBatch = collect($this->pembelian_detail_data)
                ->firstWhere('id', $this->detail_pembelian_detail_id);
            if ($selectedBatch) {
                $hargaBatchDefault = $selectedBatch['rencana_harga_jual'] ?? 0;
            }
        }
        
        // Jika tidak ada batch yang dipilih, ambil dari batch FIFO
        if ($hargaBatchDefault == 0) {
            $batch = DB::table('pembelian_detail as b')
                ->select(
                    'b.rencana_harga_jual',
                    DB::raw('(b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0) as stok_tersedia')
                )
                ->where('b.barang_id', $this->detail_barang_id)
                ->where('b.gudang_id', $this->detail_gudang_id)
                ->havingRaw('stok_tersedia > 0')
                ->orderBy('b.created_at', 'asc')
                ->first();
            
            $hargaBatchDefault = $batch->rencana_harga_jual ?? 0;
        }
        
        // Set harga berdasarkan aturan atau batch
        $this->setHargaOtomatis($hargaBatchDefault);
    }

    public function updated($field)
    {
        // Handle other field updates that don't need special processing
        if (in_array($field, ['customer_id', 'tanggal_penjualan', 'keterangan'])) {
            $this->saveToSession();
        }
    }

    public function store()
    {
        // Get current user's toko_id for validation
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            $this->error('Error', 'User tidak memiliki akses toko yang valid.');
            return;
        }
        $tokoId = $user->akses->toko_id;

        $this->validate([
            'nomor_penjualan' => [
                'required',
                'unique:penjualan,nomor_penjualan,NULL,id,toko_id,' . $tokoId
            ],
            'tanggal_penjualan' => 'required|date',
            'customer_id' => [
                'required',
                'exists:customer,id,toko_id,' . $tokoId
            ],
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        try {
            $savedPenjualanId = null;
            $latestPaymentId = null;
            
            DB::transaction(function () use (&$savedPenjualanId, &$latestPaymentId) {
                // Create Penjualan
                $penjualan = Penjualan::create([
                    'nomor_penjualan' => $this->generateNomorPenjualan_final(),
                    'tanggal_penjualan' => $this->tanggal_penjualan,
                    'customer_id' => $this->customer_id,
                    'user_id' => $this->user_id,
                    'keterangan' => $this->keterangan,
                    'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
                    'status' => $this->status,
                    'toko_id' => $this->getCurrentTokoId(),
                    'kembalian' => $this->kembalian ?? 0,
                ]);

                $savedPenjualanId = $penjualan->id;

                // Create Details
                foreach ($this->details as $detail) {
                    $penjualanDetail = PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'pembelian_detail_id' => $detail['pembelian_detail_id'],
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'gudang_id' => $detail['gudang_id'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'subtotal' => $detail['subtotal'],
                        'profit' => $detail['profit'],
                        'diskon' => $detail['diskon'] ?? 0,
                        'biaya_lain' => $detail['biaya_lain'] ?? 0,
                    ]);

                    // Update Gudang Stock (reduce stock)
                    $gudangStock = GudangStock::where([
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ])->first();

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit
                        $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                        // Reduce stock
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);

                        // Create stock transaction
                        TransaksiGudangStock::create([
                            'gudang_stock_id' => $gudangStock->id,
                            'penjualan_detail_id' => $penjualanDetail->id,
                            'jumlah' => $detail['jumlah'],
                            'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                            'tipe' => 'keluar',
                        ]);
                    }
                }

                // Create Payments (if any)
                if (count($this->payments) > 0) {
                    $totalPaid = 0;
                    $roundedTotal = round($this->total_harga / 100) * 100;

                    foreach ($this->payments as $payment) {
                        $totalPaid += $payment['jumlah'];
                        $kembalian = max(0, $totalPaid - $roundedTotal);

                        $record = PembayaranPenjualan::create([
                            'penjualan_id' => $penjualan->id,
                            'user_id' => $this->user_id,
                            'jenis_pembayaran' => $payment['jenis_pembayaran'],
                            'jumlah' => $payment['jumlah'],
                            'keterangan' => $payment['keterangan'] ?? 'Pembayaran penjualan',
                        ]);

                        $latestPaymentId = $record->id;
                    }
                }
            });

            $this->postShortcutPaymentId = $latestPaymentId;

            // Clear session after successful save
            $this->clearSession();
            
            // Show success message
            $this->success('Notifikasi', 'Berhasil menyimpan transaksi penjualan.');
            
            if ($this->postShortcutIntent === 'print-payment') {
                $this->postShortcutIntent = null;
                $targetPaymentId = $this->postShortcutPaymentId;
                $this->postShortcutPaymentId = null;

                if ($targetPaymentId) {
                    return $this->redirectRoute('penjualan.print.pembayaran', ['id' => $targetPaymentId], navigate: true);
                }
            }

            // Redirect to show page
            return $this->redirectRoute('penjualan.show', ['id' => $savedPenjualanId], navigate: true);
            
        } catch (\Exception $e) {
            Log::error('Error saving penjualan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
            return;
        }
    }

    public function update()
    {
        $this->validate([
            'nomor_penjualan' => 'required|unique:penjualan,nomor_penjualan,' . $this->penjualan_ID,
            'tanggal_penjualan' => 'required|date',
            'customer_id' => 'required|exists:customer,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        try {
            $latestPaymentId = null;

            DB::transaction(function () use (&$latestPaymentId) {
                $penjualan = Penjualan::findOrFail($this->penjualan_ID);

                // Handle stock reversal for old details before deletion
                foreach ($penjualan->penjualanDetails as $oldDetail) {
                    $stockTransaction = TransaksiGudangStock::where('penjualan_detail_id', $oldDetail->id)->first();

                    if ($stockTransaction) {
                        $gudangStock = GudangStock::find($stockTransaction->gudang_stock_id);

                        if ($gudangStock) {
                            // Calculate quantity in smallest unit to reverse
                            $jumlahTerkecil = $oldDetail->jumlah * $oldDetail->konversi_satuan_terkecil;

                            // Add back the stock (reverse the sale)
                            $gudangStock->increment('jumlah', $jumlahTerkecil);
                        }

                        // Delete the old stock transaction
                        $stockTransaction->delete();
                    }
                }

                // Delete old details and payments
                $penjualan->penjualanDetails()->delete();
                $penjualan->pembayaranPenjualan()->delete();

                // Update Penjualan
                $penjualan->update([
                    //'nomor_penjualan' => $this->nomor_penjualan,
                    'tanggal_penjualan' => $this->tanggal_penjualan,
                    'customer_id' => $this->customer_id,
                    'user_id' => $this->user_id,
                    'keterangan' => $this->keterangan,
                    'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
                    'status' => $this->status,
                    'kembalian' => $this->kembalian ?? 0,
                ]);

                // Recreate details with new stock transactions
                foreach ($this->details as $detail) {
                    $penjualanDetail = PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'pembelian_detail_id' => $detail['pembelian_detail_id'],
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'gudang_id' => $detail['gudang_id'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'subtotal' => $detail['subtotal'],
                        'profit' => $detail['profit'],
                        'diskon' => $detail['diskon'] ?? 0,
                        'biaya_lain' => $detail['biaya_lain'] ?? 0,
                    ]);

                    // Update Gudang Stock (reduce stock)
                    $gudangStock = GudangStock::where([
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ])->first();

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit
                        $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                        // Reduce stock
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);

                        // Create new stock transaction
                        TransaksiGudangStock::create([
                            'gudang_stock_id' => $gudangStock->id,
                            'penjualan_detail_id' => $penjualanDetail->id,
                            'jumlah' => $detail['jumlah'],
                            'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                            'tipe' => 'keluar',
                        ]);
                    }
                }

                // Recreate payments (if any)
                if (count($this->payments) > 0) {
                    foreach ($this->payments as $payment) {
                        PembayaranPenjualan::create([
                            'penjualan_id' => $penjualan->id,
                            'user_id' => $this->user_id,
                            'jenis_pembayaran' => $payment['jenis_pembayaran'],
                            'jumlah' => $payment['jumlah'],
                            'keterangan' => $payment['keterangan'] ?? 'Pembayaran penjualan',
                        ]);
                    }
                }
            });

            // Clear session after successful update
            $this->clearSession();
            
            // Show success message
            $this->success('Notifikasi', 'Berhasil mengupdate transaksi penjualan.');
            
            // Redirect to show page
            return $this->redirectRoute('penjualan.show', ['id' => $this->penjualan_ID],  navigate: true);
            
        } catch (\Exception $e) {
            Log::error('Error updating penjualan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
            return;
        }
    }

    public function quickFillPayment()
    {
        if ($this->sisa_pembayaran > 0) {
            $this->payment_jumlah = $this->sisa_pembayaran;
            $this->payment_keterangan = 'Pelunasan penjualan ' . $this->nomor_penjualan;
            $this->saveToSession();
            $this->success('Info', 'Jumlah pembayaran telah diisi untuk melunasi sisa pembayaran.');
        } else {
            $this->warning('Peringatan', 'Tidak ada sisa pembayaran yang perlu dilunasi.');
        }
    }

    public function printReceipt()
    {
        if ($this->type === 'create') {
            $this->warning('Info', 'Simpan transaksi terlebih dahulu sebelum mencetak struk.');
            return;
        }

        if (!$this->penjualan_ID) {
            $this->warning('Info', 'Transaksi belum memiliki ID penjualan untuk dicetak.');
            return;
        }

        return $this->redirectRoute('penjualan.print.invoice', ['id' => $this->penjualan_ID], navigate: true);
    }

    public function clearTempData()
    {
        $this->clearSession();
        $this->details = [];
        $this->payments = [];
        $this->keterangan = '';
        $this->customer_id = null;
        $this->calculateTotals();
        $this->saveToSession(); // Simpan state kosong ke session
        $this->success('Berhasil', 'Data sementara telah dihapus.');
    }

    public function addPaymentAndSave()
    {
        // Get current user's toko_id for validation
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            $this->error('Error', 'User tidak memiliki akses toko yang valid.');
            return;
        }
        $tokoId = $user->akses->toko_id;

        // Only validate payment data if user wants to add a payment
        if ($this->payment_jumlah > 0) {
            // Set default keterangan if empty
            if (empty($this->payment_keterangan)) {
                $this->payment_keterangan = 'Pembayaran penjualan ' . $this->nomor_penjualan;
            }
            
            $this->validate([
                'payment_jenis' => 'required',
                'payment_jumlah' => 'required|numeric|min:1',
            ]);
        }

        // Validate transaction data with multi-tenancy
        $this->validate([
            'nomor_penjualan' => [
                'required',
                'unique:penjualan,nomor_penjualan,NULL,id,toko_id,' . $tokoId
            ],
            'tanggal_penjualan' => 'required|date',
            'customer_id' => [
                'required',
                'exists:customer,id,toko_id,' . $tokoId
            ],
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        // Add payment to array only if payment amount is provided
        if ($this->payment_jumlah > 0) {
            $this->payments[] = [
                'id' => null,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
            ];
        }

        $this->calculateTotals();

        // Save the transaction
        try {
            $savedPenjualanId = null;
            $latestPaymentId = null;
            
            DB::transaction(function () use (&$savedPenjualanId, &$latestPaymentId) {
                // Create Penjualan
                $penjualan = Penjualan::create([
                    'nomor_penjualan' => $this->generateNomorPenjualan_final(),
                    'tanggal_penjualan' => $this->tanggal_penjualan,
                    'customer_id' => $this->customer_id,
                    'user_id' => $this->user_id,
                    'keterangan' => $this->keterangan,
                    'total_harga' => (float) round($this->total_harga / 100) * 100, // Round to nearest hundred
                    'status' => $this->status,
                    'toko_id' => $this->getCurrentTokoId(),
                    'kembalian' => $this->kembalian ?? 0,
                ]);

                $savedPenjualanId = $penjualan->id;

                // Create Details
                foreach ($this->details as $detail) {
                    $penjualanDetail = PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'pembelian_detail_id' => $detail['pembelian_detail_id'],
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'gudang_id' => $detail['gudang_id'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'subtotal' => $detail['subtotal'],
                        'profit' => $detail['profit'],
                        'diskon' => $detail['diskon'] ?? 0,
                        'biaya_lain' => $detail['biaya_lain'] ?? 0,
                    ]);

                    // Update Gudang Stock (reduce stock)
                    $gudangStock = GudangStock::where([
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ])->first();

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit
                        $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                        // Reduce stock
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);

                        // Create stock transaction
                        TransaksiGudangStock::create([
                            'gudang_stock_id' => $gudangStock->id,
                            'penjualan_detail_id' => $penjualanDetail->id,
                            'jumlah' => $detail['jumlah'],
                            'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                            'tipe' => 'keluar',
                        ]);
                    }
                }

                // Create Payments
                $totalPaid = 0;
                $roundedTotal = round($this->total_harga / 100) * 100;

                foreach ($this->payments as $payment) {
                    $totalPaid += $payment['jumlah'];
                    $kembalian = max(0, $totalPaid - $roundedTotal);

                    $record = PembayaranPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'user_id' => $this->user_id,
                        'jenis_pembayaran' => $payment['jenis_pembayaran'],
                        'jumlah' => $payment['jumlah'],
                        'keterangan' => $payment['keterangan'] ?? 'Pembayaran penjualan',
                    ]);

                    $latestPaymentId = $record->id;
                }
            });

            $this->postShortcutPaymentId = $latestPaymentId;

            // Clear session after successful save
            $this->clearSession();

            // Show success message
            if ($this->status === 'lunas') {
                $this->success('Berhasil!', 'Transaksi penjualan berhasil disimpan dengan status LUNAS!');
            } else {
                $this->success('Berhasil!', 'Transaksi penjualan berhasil disimpan dengan status ' . strtoupper(str_replace('_', ' ', $this->status)) . '.');
            }
            
            if ($this->postShortcutIntent === 'print-payment') {
                $this->postShortcutIntent = null;
                $targetPaymentId = $this->postShortcutPaymentId;
                $this->postShortcutPaymentId = null;

                if ($targetPaymentId) {
                    return $this->redirectRoute('penjualan.print.pembayaran', ['id' => $targetPaymentId], navigate: true);
                }
            }

            // Redirect to show page
            return $this->redirectRoute('penjualan.show', ['id' => $savedPenjualanId], navigate: true);
            
        } catch (\Exception $e) {
            Log::error('Error saving penjualan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
            return;
        }
    }

    public function addPaymentAndUpdate()
    {
        if ($this->payment_jumlah > 0) {
            $this->validate([
                'payment_jenis' => 'required',
                'payment_jumlah' => 'required|numeric|min:1',
                'payment_keterangan' => 'required',
            ]);
        }

        // Get current user's toko_id for validation
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            $this->error('Error', 'User tidak memiliki akses toko yang valid.');
            return;
        }
        $tokoId = $user->akses->toko_id;

        // Validate transaction data with multi-tenancy
        $this->validate([
            'nomor_penjualan' => [
                'required',
                'unique:penjualan,nomor_penjualan,' . $this->penjualan_ID . ',id,toko_id,' . $tokoId
            ],
            'tanggal_penjualan' => 'required|date',
            'customer_id' => [
                'required',
                'exists:customer,id,toko_id,' . $tokoId
            ],
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        // Check if payment would exceed total
        $new_total_payment = $this->total_payment + $this->payment_jumlah;
        // if ($new_total_payment > $this->total_harga) {
        //     $this->warning('Peringatan', 'Jumlah pembayaran melebihi total yang harus dibayar. Sisa yang perlu dibayar: Rp ' . number_format($this->sisa_pembayaran, 0, ',', '.'));
        //     return;
        // }

        // Add payment to array
        $this->payments[] = [
            'id' => null,
            'jenis_pembayaran' => $this->payment_jenis,
            'jumlah' => $this->payment_jumlah,
            'keterangan' => $this->payment_keterangan,
        ];

        $this->calculateTotals();

        try {
            $latestPaymentId = null;

            DB::transaction(function () use (&$latestPaymentId) {
                $penjualan = Penjualan::findOrFail($this->penjualan_ID);

                // Handle stock reversal for old details before deletion
                foreach ($penjualan->penjualanDetails as $oldDetail) {
                    $stockTransaction = TransaksiGudangStock::where('penjualan_detail_id', $oldDetail->id)->first();

                    if ($stockTransaction) {
                        $gudangStock = GudangStock::find($stockTransaction->gudang_stock_id);

                        if ($gudangStock) {
                            // Calculate quantity in smallest unit to reverse
                            $jumlahTerkecil = $oldDetail->jumlah * $oldDetail->konversi_satuan_terkecil;

                            // Add back the stock (reverse the sale)
                            $gudangStock->increment('jumlah', $jumlahTerkecil);
                        }

                        // Delete the old stock transaction
                        $stockTransaction->delete();
                    }
                }

                // Delete old details and payments
                $penjualan->penjualanDetails()->delete();
                $penjualan->pembayaranPenjualan()->delete();

                // Update Penjualan
                $penjualan->update([
                    // 'nomor_penjualan' => $this->nomor_penjualan,
                    'tanggal_penjualan' => $this->tanggal_penjualan,
                    'customer_id' => $this->customer_id,
                    'user_id' => $this->user_id,
                    'keterangan' => $this->keterangan,
                    'total_harga' => (float) round($this->total_harga / 100) * 100, // Round to nearest hundred
                    'status' => $this->status,
                    'kembalian' => $this->kembalian ?? 0,
                ]);

                // Recreate details with new stock transactions
                foreach ($this->details as $detail) {
                    $penjualanDetail = PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'pembelian_detail_id' => $detail['pembelian_detail_id'],
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'gudang_id' => $detail['gudang_id'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'subtotal' => $detail['subtotal'],
                        'profit' => $detail['profit'],
                        'diskon' => $detail['diskon'] ?? 0,
                        'biaya_lain' => $detail['biaya_lain'] ?? 0,
                    ]);

                    // Update Gudang Stock (reduce stock)
                    $gudangStock = GudangStock::where([
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ])->first();

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit
                        $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                        // Reduce stock
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);

                        // Create new stock transaction
                        TransaksiGudangStock::create([
                            'gudang_stock_id' => $gudangStock->id,
                            'penjualan_detail_id' => $penjualanDetail->id,
                            'jumlah' => $detail['jumlah'],
                            'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                            'tipe' => 'keluar',
                        ]);
                    }
                }

                // Recreate payments (if any)
                if (count($this->payments) > 0) {
                    $totalPaid = 0;
                    $roundedTotal = round($this->total_harga / 100) * 100;

                    foreach ($this->payments as $payment) {
                        $totalPaid += $payment['jumlah'];
                        $kembalian = max(0, $totalPaid - $roundedTotal);

                        $record = PembayaranPenjualan::create([
                            'penjualan_id' => $penjualan->id,
                            'user_id' => $this->user_id,
                            'jenis_pembayaran' => $payment['jenis_pembayaran'],
                            'jumlah' => $payment['jumlah'],
                            'keterangan' => $payment['keterangan'] ?? 'Pembayaran penjualan',
                        ]);

                        $latestPaymentId = $record->id;
                    }
                }
            });

            $this->postShortcutPaymentId = $latestPaymentId;

            // Clear session after successful update
            $this->clearSession();

            // Show success message
            if ($this->status === 'lunas') {
                $this->success('Berhasil!', 'Transaksi penjualan berhasil diupdate dengan status LUNAS!');
            } else {
                $this->success('Berhasil!', 'Transaksi penjualan berhasil diupdate dengan status ' . strtoupper(str_replace('_', ' ', $this->status)) . '.');
            }

            if ($this->postShortcutIntent === 'print-payment') {
                $this->postShortcutIntent = null;
                $targetPaymentId = $this->postShortcutPaymentId;
                $this->postShortcutPaymentId = null;

                if ($targetPaymentId) {
                    return $this->redirectRoute('penjualan.print.pembayaran', ['id' => $targetPaymentId], navigate: true);
                }
            }

            // Redirect to show page
            return $this->redirectRoute('penjualan.show', ['id' => $this->penjualan_ID],  navigate: true);
            
        } catch (\Exception $e) {
            Log::error('Error updating penjualan with payment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('Error', 'Gagal mengupdate transaksi: ' . $e->getMessage());
            return;
        }
    }

    // Modal Kasir Methods
    private function loadModalKasirHariIni()
    {
        $today = Carbon::today()->format('Y-m-d');
        $this->modal_kasir_hari_ini = ModalKasir::where('tanggal', $today)
            ->where('toko_id', $this->getCurrentTokoId())
            ->first();
        
        if (!$this->modal_kasir_hari_ini) {
            $this->show_modal_kasir_form = true;
        }
    }

    public function saveModalKasir()
    {
        $this->validate([
            'modal_kasir_jumlah' => 'required|numeric|min:0'
        ], [
            'modal_kasir_jumlah.required' => 'Jumlah modal kasir harus diisi',
            'modal_kasir_jumlah.numeric' => 'Jumlah modal kasir harus berupa angka',
            'modal_kasir_jumlah.min' => 'Jumlah modal kasir tidak boleh negatif'
        ]);

        try {
            $today = Carbon::today()->format('Y-m-d');
            
            $this->modal_kasir_hari_ini = ModalKasir::updateOrCreate(
                [
                    'tanggal' => $today,
                    'toko_id' => $this->getCurrentTokoId()
                ],
                ['modal' => $this->modal_kasir_jumlah]
            );

            $this->show_modal_kasir_form = false;
            $this->modal_kasir_jumlah = 0;
            
            $message = $this->modal_kasir_hari_ini->wasRecentlyCreated ? 'Modal kasir hari ini berhasil disimpan.' : 'Modal kasir hari ini berhasil diupdate.';
            $this->success('Berhasil!', $message);
        } catch (\Exception $e) {
            $this->error('Error!', 'Gagal menyimpan modal kasir: ' . $e->getMessage());
        }
    }

    public function cancelModalKasir()
    {
        $this->show_modal_kasir_form = false;
        $this->modal_kasir_jumlah = 0;
    }

    public function showModalKasirForm()
    {
        $this->show_modal_kasir_form = true;
        
        // Jika ada data modal kasir hari ini, isi form dengan nilai tersebut
        if ($this->modal_kasir_hari_ini) {
            $this->modal_kasir_jumlah = $this->modal_kasir_hari_ini->modal;
        } else {
            $this->modal_kasir_jumlah = 0;
        }
    }

    public function render()
    {
        try {
            // Verify data integrity before rendering
            $this->verifyDataIntegrity('render');
            
            // Final state validation before rendering
            $this->validateComponentState();
            return view('livewire.penjualan.form');
        } catch (\Exception $e) {
            Log::error('Render error in penjualan form: ' . $e->getMessage());

            // Reset component to safe state
            $this->clearSession();
            $this->details = [];
            $this->payments = [];
            $this->satuan_data = [];
            $this->pembelian_detail_data = [];
            $this->calculateTotals();

            // Show error message to user
            $this->error('Error', 'Terjadi kesalahan dalam memuat data. Data telah direset ke keadaan awal.');

            return view('livewire.penjualan.form');
        }
    }
}

/* End of file Form.php */
/* Location: ./app/Livewire/Penjualan/Form.php */
/* Created at 2025-07-03 23:22:50 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */