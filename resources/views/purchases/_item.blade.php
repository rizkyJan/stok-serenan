<div class="purchase-item" data-item-row data-row-index="{{ $index }}">
  <div class="item-header"><strong>Barang <span data-item-number>{{ $displayIndex ?? '' }}</span></strong><button type="button" class="text-danger" data-remove-item>Hapus ×</button></div>
  <div class="form-grid">
    <label>Nama / Kode Barang <span class="required">*</span>
      <select name="items[{{ $index }}][product_id]" required>
        <option value="">Pilih barang</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}" @selected(($item['product_id'] ?? null) == $p->id)>{{ $p->sku }} — {{ $p->name }} ({{ $p->unit }})</option>
        @endforeach
      </select>
    </label>
    <label>Nomor Batch <span class="required">*</span><input name="items[{{ $index }}][batch_number]" value="{{ $item['batch_number'] ?? '' }}" required maxlength="100" placeholder="Contoh: BT-0926"></label>
    <label>ED / Kedaluwarsa<input type="date" name="items[{{ $index }}][expires_at]" value="{{ $item['expires_at'] ?? '' }}"></label>
    <label>Kode ZB (opsional)<input name="items[{{ $index }}][zb_code]" maxlength="30" value="{{ $item['zb_code'] ?? '' }}" placeholder="Sesuai kolom faktur PBF"></label>
    <label>Qty / Jumlah Kemasan <span class="required">*</span><input type="number" min="1" max="1000000" name="items[{{ $index }}][purchase_quantity]" value="{{ $item['purchase_quantity'] ?? 1 }}" required data-quantity></label>
    <label>Satuan Pembelian <span class="required">*</span><input name="items[{{ $index }}][purchase_unit]" value="{{ $item['purchase_unit'] ?? 'box' }}" required maxlength="50" placeholder="Box / strip / botol"></label>
    <label>Isi per Kemasan (satuan terkecil) <span class="required">*</span><input type="number" min="1" max="1000000" name="items[{{ $index }}][unit_multiplier]" value="{{ $item['unit_multiplier'] ?? 1 }}" required data-multiplier></label>
    <label>Harga Beli / Kemasan (Rp) <span class="required">*</span><input type="number" min="0" max="9999999999" step="0.01" name="items[{{ $index }}][purchase_unit_cost]" value="{{ $item['purchase_unit_cost'] ?? 0 }}" required data-cost></label>
    <label>Diskon Barang (%)<input type="number" min="0" max="100" step="0.001" name="items[{{ $index }}][line_discount_percent]" value="{{ $item['line_discount_percent'] ?? 0 }}" data-line-percent></label>
    <label>Potongan Tambahan Barang (Rp)<input type="number" min="0" step="0.01" name="items[{{ $index }}][line_discount_amount]" value="{{ $item['line_discount_amount'] ?? 0 }}" data-line-discount placeholder="Di luar diskon persentase"></label>
  </div>
  <div class="item-summary invoice-line-summary">
    <span>Penambahan stok: <strong data-base-qty>0</strong> satuan terkecil</span>
    <span>Bruto: <strong data-line-gross>Rp 0</strong></span>
    <span>Potongan: <strong data-line-discount-total>Rp 0</strong></span>
    <span>Netto: <strong data-line-total>Rp 0</strong></span>
  </div>
  <div class="line-warning" data-line-warning hidden>Potongan tidak boleh melebihi harga bruto barang.</div>
</div>
