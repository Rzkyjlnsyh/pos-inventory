<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateSalesOrderRequest extends FormRequest
{
    public function rules()
    {
        $isDraft = $this->input('status') === 'draft';
        
        $baseRules = [
            'order_type' => ['required', 'in:jahit_sendiri,beli_jadi'],
            'order_date' => ['required', 'date'],
            'deadline' => ['nullable', 'date'], // ✅ STANDARD UNTUK SEMUA ROLE
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'payment_method' => $isDraft ? ['nullable', 'in:cash,transfer,split'] : ['required', 'in:cash,transfer,split'],
            'payment_status' => $isDraft ? ['nullable', 'in:dp,lunas'] : ['required', 'in:dp,lunas'],
            'add_to_purchase' => ['nullable', 'boolean'], // ✅ STANDARD UNTUK SEMUA ROLE
            'discount_total' => ['nullable', 'numeric', 'min:0'], // ✅ ORDER-LEVEL DISCOUNT
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];

        // ✅ PAYMENT VALIDATION STANDARD
        if (!$isDraft) {
            $baseRules['payment_amount'] = ['nullable', 'numeric', 'min:0'];
            $baseRules['cash_amount'] = ['nullable', 'numeric', 'min:0'];
            $baseRules['transfer_amount'] = ['nullable', 'numeric', 'min:0'];
            $baseRules['paid_at'] = ['nullable', 'date'];
            $baseRules['proof_path'] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
            $baseRules['reference_number'] = ['nullable', 'string', 'max:100'];
        }

        return $baseRules;
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $data = $validator->getData();
            $isDraft = ($data['status'] ?? '') === 'draft';
            
            if (!$isDraft) {
                $this->validatePaymentProof($validator, $data);
                $this->validateSplitPayment($validator, $data);
            }
        });
    }

    // ✅ STANDARD PAYMENT PROOF VALIDATION UNTUK SEMUA ROLE
    private function validatePaymentProof(Validator $validator, array $data)
    {
        if (in_array($data['payment_method'] ?? '', ['transfer', 'split'])) {
            $hasProof = !empty($data['proof_path']) || 
                       (isset($data['proof_path']) && $data['proof_path'] instanceof \Illuminate\Http\UploadedFile);
            $hasReference = !empty($data['reference_number']);

            if (!$hasProof && !$hasReference) {
                $validator->errors()->add(
                    'proof_path', 
                    'Untuk metode transfer/split, wajib upload bukti transfer ATAU isi no referensi.'
                );
            }
        }
    }

    // ✅ STANDARD SPLIT PAYMENT VALIDATION UNTUK SEMUA ROLE
    private function validateSplitPayment(Validator $validator, array $data)
    {
        if (($data['payment_method'] ?? '') === 'split') {
            $cashAmount = floatval($data['cash_amount'] ?? 0);
            $transferAmount = floatval($data['transfer_amount'] ?? 0);
            $paymentAmount = floatval($data['payment_amount'] ?? 0);

            if (abs(($cashAmount + $transferAmount) - $paymentAmount) > 0.01) {
                $validator->errors()->add(
                    'payment_amount', 
                    'Jumlah total harus sama dengan jumlah cash + transfer.'
                );
            }
        }
    }
}