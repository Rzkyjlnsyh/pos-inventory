<?php

namespace App\Imports;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shift;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesOrderImport implements ToCollection, WithHeadingRow
{
    public $errors = [];
    public $successCount = 0;
    public $importType = 'current'; // 'current' or 'historical'

    public function __construct($importType = 'current')
    {
        $this->importType = $importType;
    }

    public function collection(Collection $rows)
    {
        // ✅ UNTUK DATA HISTORICAL, SKIP SHIFT CHECK
        if ($this->importType === 'current') {
            $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
            
            if (!$activeShift) {
                $this->errors[] = 'Tidak ada shift aktif. Silakan mulai shift terlebih dahulu.';
                return;
            }
        }

        // ✅ GROUP ROWS BY SO_NUMBER UNTUK HANDLE MULTIPLE ITEMS PER SO
        $soGroups = [];
        foreach ($rows as $index => $row) {
            // Skip row kosong
            if (empty($row['so_number']) && empty($row['product_name'])) {
                continue;
            }

            $soNumber = $this->normalizeSoNumber($row['so_number'] ?? null);
            if (!$soNumber) {
                $soNumber = 'IMPORT_' . date('Ymd') . '_' . ($index + 1);
            }

            if (!isset($soGroups[$soNumber])) {
                $soGroups[$soNumber] = [
                    'so_number' => $soNumber,
                    'rows' => [],
                    'index' => $index + 2
                ];
            }
            
            $soGroups[$soNumber]['rows'][] = [
                'data' => $row,
                'index' => $index + 2
            ];
        }

        // ✅ PROCESS SETIAP GROUP SO
        foreach ($soGroups as $soGroup) {
            try {
                DB::transaction(function () use ($soGroup) {
                    $firstRow = $soGroup['rows'][0]['data'];
                    $soNumber = $soGroup['so_number'];

                    // ✅ CHECK IF SO ALREADY EXISTS
                    $existingSO = SalesOrder::where('so_number', $soNumber)->first();
                    if ($existingSO) {
                        $this->errors[] = "SO Number {$soNumber} sudah ada di sistem (Baris {$soGroup['index']})";
                        return;
                    }

                    // ✅ VALIDASI & PREPARE DATA UNTUK 1 SO
                    $validationResult = $this->validateSoData($firstRow, $soGroup['index']);
                    if (!$validationResult['valid']) {
                        $this->errors[] = $validationResult['error'];
                        return;
                    }

                    // ✅ CREATE/FIND CUSTOMER
                    $customer = $this->getOrCreateCustomer($firstRow);

                    // ✅ CALCULATE TOTALS DARI SEMUA ITEMS
                    $totals = $this->calculateSoTotals($soGroup['rows']);

                    // ✅ CREATE SALES ORDER
                    $salesOrder = SalesOrder::create([
                        'so_number' => $soNumber,
                        'order_type' => $firstRow['order_type'] ?? 'beli_jadi',
                        'order_date' => $this->parseDate($firstRow['order_date']),
                        'deadline' => !empty($firstRow['deadline']) ? $this->parseDate($firstRow['deadline']) : null,
                        'customer_id' => $customer->id,
                        'subtotal' => $totals['subtotal'],
                        'discount_total' => $totals['discount_total'],
                        'shipping_cost' => $totals['shipping_cost'],
                        'grand_total' => $totals['grand_total'],
                        'status' => $this->importType === 'historical' ? 'selesai' : 'pending',
                        'payment_method' => $firstRow['payment_method'] ?? 'cash',
                        'payment_status' => $firstRow['payment_status'] ?? 'dp',
                        'created_by' => Auth::id(),
                        'approved_by' => $this->importType === 'historical' ? Auth::id() : null,
                        'approved_at' => $this->importType === 'historical' ? now() : null,
                        'completed_at' => $this->importType === 'historical' ? now() : null,
                    ]);

                    // ✅ CREATE SALES ORDER ITEMS
                    foreach ($soGroup['rows'] as $itemData) {
                        $row = $itemData['data'];
                        
                        SalesOrderItem::create([
                            'sales_order_id' => $salesOrder->id,
                            'product_id' => null, // Manual product untuk import
                            'product_name' => $row['product_name'],
                            'sku' => $row['sku'] ?? null,
                            'sale_price' => (float) $row['sale_price'],
                            'qty' => (int) $row['qty'],
                            'discount' => (float) ($row['discount'] ?? 0),
                            'line_total' => ((float) $row['sale_price'] * (int) $row['qty']) - (float) ($row['discount'] ?? 0),
                        ]);
                    }

                    // ✅ CREATE PAYMENT JIKA ADA DATA PEMBAYARAN
                    if (!empty($firstRow['payment_amount']) && (float)$firstRow['payment_amount'] > 0) {
                        $this->createPayment($salesOrder, $firstRow);
                    }

                    $this->successCount++;
                    
                    // ✅ CREATE LOG
                    \App\Models\SalesOrderLog::create([
                        'sales_order_id' => $salesOrder->id,
                        'user_id' => Auth::id(),
                        'action' => 'created',
                        'description' => "Sales order diimport dari Excel: {$soNumber}",
                        'created_at' => now(),
                    ]);
                });

            } catch (\Exception $e) {
                $this->errors[] = "SO {$soGroup['so_number']} (Baris {$soGroup['index']}): " . $e->getMessage();
            }
        }
    }

    // ✅ HELPER METHODS
    private function normalizeSoNumber($soNumber)
    {
        if (empty($soNumber)) {
            return $this->generateSoNumber();
        }
        
        $soNumber = strtoupper(trim($soNumber));
        
        // Jika SO number tidak ada prefix, tambahkan
        if (!preg_match('/^[A-Za-z]/', $soNumber)) {
            $soNumber = 'SAL' . $soNumber;
        }
        
        return $soNumber;
    }

    private function validateSoData($row, $rowIndex)
    {
        $requiredFields = ['order_date', 'customer_name', 'product_name', 'sale_price', 'qty'];
        
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                return [
                    'valid' => false,
                    'error' => "Baris {$rowIndex}: Field {$field} harus diisi"
                ];
            }
        }

        // Validasi order_type
        if (!empty($row['order_type']) && !in_array($row['order_type'], ['jahit_sendiri', 'beli_jadi'])) {
            return [
                'valid' => false,
                'error' => "Baris {$rowIndex}: ORDER_TYPE harus 'jahit_sendiri' atau 'beli_jadi'"
            ];
        }

        // Validasi numeric fields
        if (!is_numeric($row['sale_price']) || $row['sale_price'] <= 0) {
            return [
                'valid' => false,
                'error' => "Baris {$rowIndex}: SALE_PRICE harus angka positif"
            ];
        }

        if (!is_numeric($row['qty']) || $row['qty'] <= 0) {
            return [
                'valid' => false,
                'error' => "Baris {$rowIndex}: QTY harus angka positif"
            ];
        }

        return ['valid' => true];
    }

    private function getOrCreateCustomer($row)
    {
        $customerName = $row['customer_name'];
        $customerPhone = $row['customer_phone'] ?? null;

        $customer = Customer::where('name', $customerName)->first();
        
        if (!$customer) {
            $customer = Customer::create([
                'name' => $customerName,
                'phone' => $customerPhone,
                'email' => null,
                'address' => null,
                'notes' => 'Auto-created from import',
                'is_active' => true
            ]);
        }

        return $customer;
    }

    private function calculateSoTotals($rows)
    {
        $subtotal = 0;
        $discount_total = 0;
        $shipping_cost = 0;

        foreach ($rows as $itemData) {
            $row = $itemData['data'];
            $salePrice = (float) $row['sale_price'];
            $qty = (int) $row['qty'];
            $discount = (float) ($row['discount'] ?? 0);
            
            $subtotal += ($salePrice * $qty);
            $discount_total += $discount;
        }

        // Ambil shipping cost dari row pertama (jika ada)
        $firstRow = $rows[0]['data'];
        $shipping_cost = !empty($firstRow['shipping_cost']) ? (float) $firstRow['shipping_cost'] : 0;

        $grand_total = $subtotal - $discount_total + $shipping_cost;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discount_total,
            'shipping_cost' => $shipping_cost,
            'grand_total' => $grand_total
        ];
    }

    private function createPayment($salesOrder, $row)
    {
        $paymentAmount = (float) $row['payment_amount'];
        $paymentMethod = $row['payment_method'] ?? 'cash';
        
        $cashAmount = 0;
        $transferAmount = 0;

        if ($paymentMethod === 'split') {
            $cashAmount = (float) ($row['cash_amount'] ?? 0);
            $transferAmount = (float) ($row['transfer_amount'] ?? 0);
        } elseif ($paymentMethod === 'cash') {
            $cashAmount = $paymentAmount;
        } else {
            $transferAmount = $paymentAmount;
        }

        Payment::create([
            'sales_order_id' => $salesOrder->id,
            'method' => $paymentMethod,
            'status' => $paymentAmount >= $salesOrder->grand_total ? 'lunas' : 'dp',
            'category' => $paymentAmount >= $salesOrder->grand_total ? 'pelunasan' : 'dp',
            'amount' => $paymentAmount,
            'cash_amount' => $cashAmount,
            'transfer_amount' => $transferAmount,
            'paid_at' => $this->parseDate($row['paid_at'] ?? $row['order_date']),
            'reference_number' => $row['reference_number'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return now();
        }

        try {
            // Coba parse berbagai format date
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return now();
        }
    }

    private function generateSoNumber(): string
    {
        $date = Carbon::now()->format('ymd');
        $seq = DB::table('sales_orders')->whereDate('created_at', Carbon::today())->count() + 1;
        return 'SAL' . $date . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}