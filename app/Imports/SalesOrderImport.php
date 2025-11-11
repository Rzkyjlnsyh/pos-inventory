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

    public function collection(Collection $rows)
    {
        $activeShift = Shift::where('user_id', Auth::id())->whereNull('end_time')->first();
        
        if (!$activeShift) {
            $this->errors[] = 'Tidak ada shift aktif. Silakan mulai shift terlebih dahulu.';
            return;
        }

        foreach ($rows as $index => $row) {
            try {
                // Skip row kosong
                if (empty($row['so_number']) && empty($row['product_name'])) {
                    continue;
                }

                // Validasi required fields
                $requiredFields = ['order_date', 'customer_name', 'order_type', 'payment_method', 'product_name', 'sale_price', 'qty'];
                foreach ($requiredFields as $field) {
                    if (empty($row[$field])) {
                        $this->errors[] = "Baris " . ($index + 2) . ": Field {$field} harus diisi";
                        continue 2;
                    }
                }

                // Validasi order_type
                if (!in_array($row['order_type'], ['jahit_sendiri', 'beli_jadi'])) {
                    $this->errors[] = "Baris " . ($index + 2) . ": ORDER_TYPE harus 'jahit_sendiri' atau 'beli_jadi'";
                    continue;
                }

                // Validasi payment_method
                if (!in_array($row['payment_method'], ['cash', 'transfer', 'split'])) {
                    $this->errors[] = "Baris " . ($index + 2) . ": PAYMENT_METHOD harus 'cash', 'transfer', atau 'split'";
                    continue;
                }

                // Validasi payment_status
                if (!in_array($row['payment_status'] ?? 'dp', ['dp', 'lunas'])) {
                    $this->errors[] = "Baris " . ($index + 2) . ": PAYMENT_STATUS harus 'dp' atau 'lunas'";
                    continue;
                }

                // Validasi numeric fields
                if (!is_numeric($row['sale_price']) || $row['sale_price'] <= 0) {
                    $this->errors[] = "Baris " . ($index + 2) . ": SALE_PRICE harus angka positif";
                    continue;
                }

                if (!is_numeric($row['qty']) || $row['qty'] <= 0) {
                    $this->errors[] = "Baris " . ($index + 2) . ": QTY harus angka positif";
                    continue;
                }

                if (!empty($row['discount']) && (!is_numeric($row['discount']) || $row['discount'] < 0)) {
                    $this->errors[] = "Baris " . ($index + 2) . ": DISCOUNT harus angka positif atau 0";
                    continue;
                }

                // Process sales order
                DB::transaction(function () use ($row, $activeShift, $index) {
                    $soNumber = $row['so_number'] ?? $this->generateSoNumber();
                    
                    // Check if SO already exists
                    $existingSO = SalesOrder::where('so_number', $soNumber)->first();
                    if ($existingSO) {
                        $salesOrder = $existingSO;
                    } else {
                        // Create or find customer
                        $customer = Customer::firstOrCreate(
                            ['name' => $row['customer_name']],
                            [
                                'phone' => $row['customer_phone'] ?? null,
                                'email' => null,
                                'address' => null,
                                'notes' => 'Auto-created from import',
                                'is_active' => true
                            ]
                        );

                        // Calculate item totals
                        $salePrice = (float) $row['sale_price'];
                        $qty = (int) $row['qty'];
                        $discount = (float) ($row['discount'] ?? 0);
                        $lineTotal = ($salePrice * $qty) - $discount;

                        // Create sales order
                        $salesOrder = SalesOrder::create([
                            'so_number' => $soNumber,
                            'order_type' => $row['order_type'],
                            'order_date' => Carbon::parse($row['order_date']),
                            'deadline' => !empty($row['deadline']) ? Carbon::parse($row['deadline']) : null,
                            'customer_id' => $customer->id,
                            'subtotal' => $lineTotal,
                            'discount_total' => $discount,
                            'grand_total' => $lineTotal,
                            'status' => 'pending',
                            'payment_method' => $row['payment_method'],
                            'payment_status' => $row['payment_status'] ?? 'dp',
                            'created_by' => Auth::id(),
                        ]);
                    }

                    // Create sales order item
                    SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'product_id' => null, // Manual product
                        'product_name' => $row['product_name'],
                        'sku' => $row['sku'] ?? null,
                        'sale_price' => (float) $row['sale_price'],
                        'qty' => (int) $row['qty'],
                        'discount' => (float) ($row['discount'] ?? 0),
                        'line_total' => ($row['sale_price'] * $row['qty']) - ($row['discount'] ?? 0),
                    ]);

                    // Update sales order totals if existing SO
                    if ($existingSO) {
                        $this->updateSalesOrderTotals($salesOrder);
                    }

                    $this->successCount++;
                });

            } catch (\Exception $e) {
                $this->errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    private function generateSoNumber(): string
    {
        $date = Carbon::now()->format('ymd');
        $seq = DB::table('sales_orders')->whereDate('created_at', Carbon::today())->count() + 1;
        return 'SAL' . $date . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function updateSalesOrderTotals(SalesOrder $salesOrder): void
    {
        $subtotal = $salesOrder->items->sum('line_total');
        $discountTotal = $salesOrder->items->sum('discount');
        $grandTotal = $subtotal - $discountTotal;

        $salesOrder->update([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
        ]);
    }
}