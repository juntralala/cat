<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'string', 'exists:items,id'],
            'sku' => ['required', 'string', 'unique:skus,sku'],
            'spesification_name' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku_measurement_unit_conversions' => ['nullable', 'array', 'list'],
            'sku_measurement_unit_conversions.*.measurement_unit_id' => ['sometimes', 'required', 'exists:measurement_units,id', 'distinct:ignore_case'],
            'sku_measurement_unit_conversions.*.conversion' => ['sometimes', 'required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Barang harus dipilih.',
            'item_id.exists' => 'Barang tidak ditemukan.',
            'sku.required' => 'SKU harus diisi.',
            'sku.unique' => 'SKU sudah digunakan.',
            'spesification_name.required' => 'Nama spesifikasi harus diisi.',
            'quantity.required' => 'Stok harus diisi.',
            'quantity.min' => 'Stok tidak boleh kurang dari 0.',
            'price.required' => 'Harga harus diisi.',
            'price.min' => 'Harga tidak boleh kurang dari 0.',
            'sku_measurement_unit_conversions.*.measurement_unit_id.required' => 'Satuan harus dipilih.',
            'sku_measurement_unit_conversions.*.measurement_unit_id.exists' => 'Satuan tidak tidak terdaftar.',
            'sku_measurement_unit_conversions.*.measurement_unit_id.distinct' => 'Satuan tidak boleh duplikat.',
            'sku_measurement_unit_conversions.*.conversion.required' => 'Konversi harus diisi.',
            'sku_measurement_unit_conversions.*.conversion.min' => 'Konversi minimal 1.',
        ];
    }
}
