@extends('layouts.admin')

@section('title', 'Manajemen Produk - Admin')

@section('content')
<div class="page-header" style="display:flex; align-items:center; gap:1rem;">
    <h1 style="margin:0; display:flex; align-items:center; gap:0.5rem;"><i class="fas fa-box"></i> Manajemen Produk</h1>

    <div style="margin-left:auto; display:flex; gap:0.5rem; align-items:center;">
        <a href="{{ route('admin.products.create') }}" class="header-btn header-add">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>

        <form id="exportFormHeader" action="{{ route('admin.products.exportPriceTemplate') }}" method="GET" style="margin:0;">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">
            <button type="submit" class="header-btn header-export">
                <i class="fas fa-file-excel"></i> Export Produk
            </button>
        </form>

        <form id="importFormHeader" action="{{ route('admin.products.importPriceUpdates') }}" method="POST" enctype="multipart/form-data" style="margin:0;">
            @csrf
            <input type="file" id="priceFileHeader" name="file" accept=".xlsx,.xls,.csv,text/csv" style="display:none;">
            <button type="button" id="importBtnHeader" class="header-btn header-import">
                <i class="fas fa-upload"></i> Import Produk
            </button>
        </form>

        <details style="position:relative;">
            <summary class="header-btn header-import" style="padding:0.4rem 0.7rem; cursor:pointer; list-style:none;">
                <i class="fas fa-history"></i> Riwayat File
            </summary>
            <div class="card" style="position:absolute; right:0; top:calc(100% + 0.4rem); min-width:260px; z-index:20; box-shadow:0 8px 24px rgba(0,0,0,0.12);">
                <div class="card-body" style="padding:0.8rem;">
                    <div style="font-size:0.8rem; font-weight:700; color:var(--gray-600); margin-bottom:0.5rem;">Riwayat file</div>
                    @if(!empty($fileHistory))
                        <ul style="margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:0.45rem;">
                            @foreach(array_reverse($fileHistory) as $item)
                                <li style="font-size:0.85rem; color:var(--gray-700); border-bottom:1px solid var(--gray-200); padding-bottom:0.35rem;">
                                    <div style="font-weight:600; text-transform:capitalize;">{{ $item['type'] }}</div>
                                    <div>{{ $item['filename'] }}</div>
                                    @if(!empty($item['updates']) || !empty($item['before']))
                                        <div style="display:flex; align-items:center; justify-content:space-between; gap:0.4rem; margin-top:0.35rem; flex-wrap:wrap;">
                                            <span style="font-size:0.72rem; color:var(--gray-500);">{{ isset($item['count']) ? $item['count'] . ' item' : '1 item' }}</span>
                                            <form action="{{ route('admin.products.undoFileHistory') }}" method="POST" class="undo-history-form" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="history_id" value="{{ $item['id'] }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary undo-history-btn" data-name="{{ $item['filename'] }}" style="padding:0.2rem 0.5rem; font-size:0.75rem;">
                                                    <i class="fas fa-undo"></i> Undo
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div style="font-size:0.85rem; color:var(--gray-500);">Belum ada riwayat file.</div>
                    @endif

                </div>
            </div>
        </details>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0.75rem 1.25rem;">
        <form action="{{ route('admin.products.index') }}" method="GET" style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px; margin:0;">
                <input type="text" id="productSearch" name="search" placeholder="Cari produk..." value="{{ request('search') }}" autocomplete="off">
            </div>
            <div class="form-group" style="min-width:160px; margin:0;">
                <select name="category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="min-width:130px; margin:0;">
                <select name="status">  
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="form-group" style="min-width:140px; margin:0;">
                <select name="sort">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            @if(request()->hasAny(['search', 'category', 'status', 'sort']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
            @endif
        </form>
    </div>
</div>

<!-- Import form moved to header for cleaner layout -->

<div class="card" id="productListCard">
    {{-- Bulk Action Bar --}}
    <div id="bulkBar" style="display:none; background:#1e40af; color:#fff; padding:0.65rem 1.25rem; border-radius:var(--radius-md) var(--radius-md) 0 0; display:none; align-items:center; gap:1rem; flex-wrap:wrap;">
        <span id="bulkCount" style="font-weight:700; font-size:0.9rem;">0 produk dipilih</span>
        <div style="display:flex; gap:0.5rem; margin-left:auto;">
            <button onclick="bulkToggle('active')" class="btn btn-sm" style="background:#22c55e; color:#fff; border:none;">
                <i class="fas fa-check"></i> Aktifkan
            </button>
            <button onclick="bulkToggle('inactive')" class="btn btn-sm" style="background:#f59e0b; color:#fff; border:none;">
                <i class="fas fa-times"></i> Nonaktifkan
            </button>
            <button onclick="bulkExport()" class="btn btn-sm" style="background:#0ea5e9; color:#fff; border:none;">
                <i class="fas fa-file-export"></i> Export
            </button>
            <button onclick="bulkDelete()" class="btn btn-sm" style="background:#ef4444; color:#fff; border:none;">
                <i class="fas fa-trash"></i> Hapus
            </button>
            <button onclick="clearSelection()" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:#fff; border:1px solid rgba(255,255,255,0.4);">
                <i class="fas fa-times"></i> Batal
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:2.5rem; text-align:center;">
                        <input type="checkbox" id="selectAll" title="Pilih semua" style="cursor:pointer; width:1rem; height:1rem;">
                    </th>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kode</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Berat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                    <tr class="product-row" data-url="{{ route('admin.products.edit', $product) }}" data-id="{{ $product->id }}">
                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <input type="checkbox" class="row-check" value="{{ $product->id }}" style="cursor:pointer; width:1rem; height:1rem;">
                        </td>
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td>
                            @if($product->image)
                                <div style="width:80px; height:80px; background:var(--gray-50); border-radius:var(--radius-sm); border:1px solid var(--gray-200); overflow:hidden; cursor:pointer;" onclick="openImgPreview(this.querySelector('img'))">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:100%; height:100%; object-fit:contain; padding:4px;">
                                </div>
                            @else
                                <div style="width:80px; height:80px; background:var(--gray-100); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; color:var(--gray-400); font-size:1.5rem;">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>
                            @if($product->product_code)
                                <code style="font-size:0.75rem; background:var(--gray-100); padding:0.15rem 0.4rem; border-radius:4px; color:var(--gray-700);">{{ $product->product_code }}</code>
                            @else
                                <span style="color:var(--gray-300);">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-status badge-processing">{{ $product->category->name ?? '-' }}</span>
                        </td>
                        <td>
                            @if($product->hasDiscount())
                                <span style="text-decoration:line-through; color:var(--gray-400); font-size:0.8rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</span><br>
                                <strong style="color:var(--danger);">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</strong>
                            @else
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            @endif
                        </td>
                        <td>
                            @if($product->isLowStock())
                                <span class="badge-status badge-cancelled" title="Stok menipis!"><i class="fas fa-exclamation-triangle"></i> {{ $product->stock }}</span>
                            @else
                                <span>{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($product->weight, 0) }}g</td>
                        <td>
                            <button class="btn btn-sm {{ $product->is_active ? 'btn-success' : 'btn-secondary' }} toggle-active"
                                    data-url="{{ route('admin.products.toggleActive', $product) }}">
                                <i class="fas {{ $product->is_active ? 'fa-check' : 'fa-times' }}"></i>
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-icon btn-danger delete-btn"
                                        data-name="{{ $product->name }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>Belum ada produk</h3>
                                <p>Mulai tambahkan produk ke katalog</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .product-row { transition: background-color 0.15s ease; }
    .product-row:hover {
        background-color: var(--gray-50);
        cursor: pointer;
    }
    .page-header .btn { white-space: nowrap; }
    .page-header .btn + .btn { margin-left: 0.5rem; }
    /* Header button styles - clean and consistent */
    .header-btn {
        display:inline-flex;
        align-items:center;
        gap:0.5rem;
        padding:0.45rem 0.7rem;
        border-radius:0.375rem;
        font-size:0.95rem;
        text-decoration:none;
        cursor:pointer;
        border:1px solid transparent;
        transition:all 0.12s ease;
    }
    .header-btn i { font-size:0.95rem; }
    .header-add {
        background:#2563eb; /* blue-600, sama dengan tombol Filter */
        color:#ffffff;
        border-color:#2563eb;
    }
    .header-add:hover {
        background:#1d4ed8;
        border-color:#1d4ed8;
        color:#ffffff;
    }
    .header-export {
        background:#16a34a; /* green-600 */
        color:#ffffff;
        border-color:#16a34a;
    }
    .header-export:hover {
        background:#15803d;
        border-color:#15803d;
        color:#ffffff;
    }
    .header-import {
        background:#16a34a; /* green-600 */
        color:#ffffff;
        border-color:#16a34a;
    }
    .header-import:hover {
        background:#15803d;
        border-color:#15803d;
        color:#ffffff;
    }
</style>
@endpush

@push('scripts')
<script>
// Import button in header: trigger file input and auto-submit
document.addEventListener('DOMContentLoaded', function() {
    var importBtn = document.getElementById('importBtnHeader');
    var fileInput = document.getElementById('priceFileHeader');
    var form = document.getElementById('importFormHeader');

    if (importBtn && fileInput && form) {
        importBtn.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                form.submit();
            }
        });
    }
});

function openImgPreview(img) {
    if (img.classList.contains('img-zoomed')) return;
    img.classList.add('img-zoomed');
    setTimeout(function() {
        document.addEventListener('click', function closePreview() {
            img.classList.remove('img-zoomed');
            document.removeEventListener('click', closePreview);
        });
    }, 0);
}

// Bulk Select
var STORAGE_KEY = 'adminProductSelectedIds';

function getStoredSelectedIds() {
    try {
        var raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        return [];
    }
}

function setStoredSelectedIds(ids) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
}

function getChecked() {
    return getStoredSelectedIds();
}

function updateBulkBar() {
    var bulkBar = document.getElementById('bulkBar');
    var bulkCount = document.getElementById('bulkCount');
    if (!bulkBar || !bulkCount) return;
    var ids = getChecked();
    var visibleCheckboxes = [].slice.call(document.querySelectorAll('.row-check'));
    if (ids.length > 0) {
        bulkBar.style.display = 'flex';
        bulkCount.textContent = ids.length + ' produk dipilih';
    } else {
        bulkBar.style.display = 'none';
    }
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        var checkedVisible = visibleCheckboxes.filter(function(c) { return c.checked; }).length;
        selectAll.indeterminate = checkedVisible > 0 && checkedVisible < visibleCheckboxes.length;
        selectAll.checked = visibleCheckboxes.length > 0 && checkedVisible === visibleCheckboxes.length;
    }
}

function syncSelectionFromStorage() {
    var selectedIds = getStoredSelectedIds();
    document.querySelectorAll('.row-check').forEach(function(cb) {
        cb.checked = selectedIds.indexOf(cb.value) >= 0;
    });
    updateBulkBar();
}

function clearSelection() {
    setStoredSelectedIds([]);
    document.querySelectorAll('.row-check').forEach(function(c) { c.checked = false; });
    updateBulkBar();
}

// ===== AJAX live list =====
var productSearchInput = document.getElementById('productSearch');
var productListCard = document.getElementById('productListCard');

function listSelectVal(name) {
    var el = document.querySelector('select[name="' + name + '"]');
    return el ? el.value : '';
}

function buildListUrl() {
    var url = new URL(window.location.origin + window.location.pathname);
    var search = productSearchInput ? productSearchInput.value.trim() : '';
    if (search) { url.searchParams.set('search', search); } else { url.searchParams.delete('search'); }
    var category = listSelectVal('category');
    if (category) { url.searchParams.set('category', category); } else { url.searchParams.delete('category'); }
    var status = listSelectVal('status');
    if (status) { url.searchParams.set('status', status); } else { url.searchParams.delete('status'); }
    var sort = listSelectVal('sort');
    if (sort) { url.searchParams.set('sort', sort); } else { url.searchParams.delete('sort'); }
    url.searchParams.delete('page');
    return url;
}

function loadList(url) {
    return fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(res) {
        if (!res.ok) throw new Error('Gagal memuat data');
        return res.text();
    })
    .then(function(html) {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        var replacement = wrapper.querySelector('#productListCard');
        if (!replacement) throw new Error('Konten tidak ditemukan');
        productListCard.outerHTML = replacement.outerHTML;
        productListCard = document.getElementById('productListCard');
        bindTableEvents();
    });
}

function refreshList() {
    var url = buildListUrl();
    updateFilterButtons();
    loadList(url).catch(function() {
        window.location.href = url.toString();
    });
}

function updateFilterButtons() {
    var hasFilter = !!(productSearchInput && productSearchInput.value.trim())
        || !!listSelectVal('category')
        || !!listSelectVal('status');
    var resetLink = productSearchInput ? productSearchInput.closest('form').querySelector('a.btn-secondary') : null;
    if (resetLink) {
        resetLink.style.display = hasFilter ? '' : 'none';
    }
}

// ===== Bind ulang event tabel (dipanggil ulang tiap selesai AJAX) =====
function bindTableEvents() {
    document.querySelectorAll('.product-row').forEach(function(row) {
        if (row.dataset.bound) return;
        row.dataset.bound = '1';
        row.addEventListener('click', function(e) {
            if (e.target.closest('button, a, form, .toggle-active, .delete-btn, [onclick]')) return;
            window.location = this.dataset.url;
        });
    });

    document.querySelectorAll('.toggle-active').forEach(function(btn) {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', function() {
            var url = this.dataset.url;
            var button = this;
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    if (data.is_active) {
                        button.className = 'btn btn-sm btn-success toggle-active';
                        button.innerHTML = '<i class="fas fa-check"></i> Aktif';
                    } else {
                        button.className = 'btn btn-sm btn-secondary toggle-active';
                        button.innerHTML = '<i class="fas fa-times"></i> Nonaktif';
                    }
                }
            });
        });
    });

    var selectAll = document.getElementById('selectAll');
    if (selectAll && !selectAll.dataset.bound) {
        selectAll.dataset.bound = '1';
        selectAll.addEventListener('change', function() {
            var visibleCheckboxes = document.querySelectorAll('.row-check');
            var selectedIds = getStoredSelectedIds();
            visibleCheckboxes.forEach(function(c) {
                c.checked = this.checked;
                var id = c.value;
                var idx = selectedIds.indexOf(id);
                if (this.checked) {
                    if (idx < 0) selectedIds.push(id);
                } else if (idx >= 0) {
                    selectedIds.splice(idx, 1);
                }
            }, this);
            setStoredSelectedIds(selectedIds);
            updateBulkBar();
        });
    }

    document.querySelectorAll('.row-check').forEach(function(c) {
        if (c.dataset.bound) return;
        c.dataset.bound = '1';
        c.addEventListener('change', function() {
            var id = this.value;
            var selectedIds = getStoredSelectedIds();
            var idx = selectedIds.indexOf(id);
            if (this.checked) {
                if (idx < 0) selectedIds.push(id);
            } else if (idx >= 0) {
                selectedIds.splice(idx, 1);
            }
            setStoredSelectedIds(selectedIds);
            updateBulkBar();
        });
    });

    var card = document.getElementById('productListCard');
    if (card && !card.dataset.pageBound) {
        card.dataset.pageBound = '1';
        card.addEventListener('click', function(e) {
            var link = e.target.closest('.pagination a');
            if (!link) return;
            e.preventDefault();
            loadList(link.href).catch(function() {
                window.location.href = link.href;
            });
        });
    }

    syncSelectionFromStorage();
}

function bulkDelete() {
    const ids = getChecked();
    if (!ids.length) return;
    wwConfirm(
        'Hapus ' + ids.length + ' Produk?',
        'Produk yang dipilih akan dihapus secara permanen dan tidak dapat dikembalikan.',
        function() {
            fetch('{{ route('admin.products.bulkDelete') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids })
            })
            .then(r => r.json())
            .then(d => { if (d.success) window.location.reload(); });
        }
    );
}

function bulkExport() {
    const ids = getChecked();
    if (!ids.length) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.products.exportSelected') }}';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = '_token';
    input.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(input);

    ids.forEach(id => {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'ids[]';
        hidden.value = id;
        form.appendChild(hidden);
    });

    document.body.appendChild(form);
    form.submit();
}

function bulkToggle(status) {
    const ids = getChecked();
    if (!ids.length) return;
    fetch('{{ route('admin.products.bulkToggle') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids, status })
    })
    .then(r => r.json())
    .then(d => { if (d.success) window.location.reload(); });
}

// Delete confirm
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.delete-btn');
    if (!btn) return;
    var form = btn.closest('.delete-form');
    var name = btn.dataset.name || 'item ini';
    wwConfirm(
        'Hapus Produk?',
        'Produk "' + name + '" akan dihapus secara permanen.',
        function() { form.submit(); }
    );
});

// ===== Search / filter (AJAX, tanpa reload halaman) =====
(function() {
    if (!productSearchInput || !productListCard) return;

    var timer;
    productSearchInput.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(refreshList, 300);
    });

    ['category', 'status', 'sort'].forEach(function(name) {
        var el = document.querySelector('select[name="' + name + '"]');
        if (el) el.addEventListener('change', refreshList);
    });

    var filterForm = productSearchInput.closest('form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            refreshList();
        });
        var resetLink = filterForm.querySelector('a.btn-secondary');
        if (resetLink) {
            resetLink.addEventListener('click', function(e) {
                e.preventDefault();
                productSearchInput.value = '';
                ['category', 'status', 'sort'].forEach(function(name) {
                    var el = document.querySelector('select[name="' + name + '"]');
                    if (el) el.value = '';
                });
                refreshList();
            });
        }
    }
})();

// ===== Undo riwayat file =====
document.addEventListener('submit', function(e) {
    var form = e.target.closest('.undo-history-form');
    if (!form) return;
    e.preventDefault();
    var btn = form.querySelector('.undo-history-btn');
    var name = (btn && btn.dataset.name) || 'file ini';
    wwConfirm(
        'Undo Riwayat File?',
        'Perubahan dari file "' + name + '" akan dikembalikan. Lanjutkan?',
        function() { form.submit(); },
        { confirmText: 'Ya, Undo', confirmColor: '#2563eb' }
    );
});

// Inisialisasi
bindTableEvents();
</script>
@endpush
