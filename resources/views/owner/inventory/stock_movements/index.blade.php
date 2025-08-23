<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock Movements - Custom Pare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Nunito,sans-serif'; }
  </style>
</head>
<body class="bg-gray-100">
  <div class="flex-1 lg:w-5/6 ml-0">
    <div class="p-4 lg:p-8">
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Stock Movements</h3>

          <!-- Form filter tanggal -->
          <form method="GET" class="flex space-x-2">
            <input type="date" name="from" value="{{ $from }}"
                   class="border rounded px-2 py-1">
            <input type="date" name="to" value="{{ $to }}"
                   class="border rounded px-2 py-1">
            <button type="submit"
                    class="bg-[#005281] text-white px-4 py-1 rounded hover:bg-[#00446a]">
              <i class="bi bi-funnel"></i> Filter
            </button>
            <a href="{{ route('owner.inventory.stock-movements.index') }}" 
               class="bg-gray-300 text-gray-700 px-4 py-1 rounded hover:bg-gray-400 flex items-center">
              <i class="bi bi-arrow-clockwise"></i> Reset
            </a>
          </form>
        </div>

        @if(session('success'))
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
          </div>
        @endif

        <div class="overflow-x-auto">
          <table class="min-w-full border rounded-lg text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Produk</th>
                <th class="px-4 py-2">Stok Awal</th>
                <th class="px-4 py-2 text-green-600">Masuk</th>
                <th class="px-4 py-2 text-red-600">Keluar</th>
                <th class="px-4 py-2">Stok Akhir</th>
                <th class="px-4 py-2">Transaksi</th>
                <th class="px-4 py-2">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($stockMovements as $move)
              <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($move->movement_date)->format('d M Y') }}</td>
                <td class="px-4 py-2">{{ $move->product->name ?? '-' }}</td>
                <td class="px-4 py-2">{{ $move->initial_qty }}</td>
                <td class="px-4 py-2 text-green-600 font-semibold">+{{ $move->total_in }}</td>
                <td class="px-4 py-2 text-red-600 font-semibold">-{{ $move->total_out }}</td>
                <td class="px-4 py-2 font-semibold">{{ $move->final_qty }}</td>
                <td class="px-4 py-2 text-center">{{ $move->transaction_count }}</td>
                <td class="px-4 py-2">
                  <button onclick="showMovementDetails('{{ $move->product_id }}', '{{ $move->movement_date }}')"
                          class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                    <i class="bi bi-eye"></i> Detail
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $stockMovements->links() }}
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="movementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex justify-between items-center pb-3 border-b">
          <h3 class="text-xl font-semibold" id="modalTitle">Detail Pergerakan Stok</h3>
          <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mt-4">
          <div id="modalContent" class="max-h-96 overflow-y-auto">
            <!-- Content will be loaded via AJAX -->
          </div>
        </div>
        
        <div class="mt-4 flex justify-end">
          <button onclick="closeModal()" 
                  class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
  function showMovementDetails(productId, date) {
      // Show loading
      document.getElementById('modalContent').innerHTML = '<div class="text-center py-8">Loading...</div>';
      document.getElementById('movementModal').classList.remove('hidden');
      
      // Fetch data via AJAX
      fetch(`/owner/inventory/stock-movements/${productId}/${date}`)
          .then(response => {
              if (!response.ok) {
                  throw new Error('Network response was not ok');
              }
              return response.json();
          })
          .then(data => {
              let html = `
                  <h4 class="font-semibold mb-4">${data.product} - ${new Date(data.date).toLocaleDateString('id-ID')}</h4>
                  <div class="overflow-x-auto">
                      <table class="min-w-full divide-y divide-gray-200 text-sm">
                          <thead class="bg-gray-50">
                              <tr>
                                  <th class="px-4 py-2 text-left">Waktu</th>
                                  <th class="px-4 py-2 text-left">Tipe</th>
                                  <th class="px-4 py-2 text-left">Ref</th>
                                  <th class="px-4 py-2 text-left">Awal</th>
                                  <th class="px-4 py-2 text-left">Masuk</th>
                                  <th class="px-4 py-2 text-left">Keluar</th>
                                  <th class="px-4 py-2 text-left">Akhir</th>
                                  <th class="px-4 py-2 text-left">User</th>
                                  <th class="px-4 py-2 text-left">Catatan</th>
                              </tr>
                          </thead>
                          <tbody>
              `;
              
              data.movements.forEach(movement => {
                  let typeBadge = '';
                  let typeText = movement.type;
                  
                  switch(movement.type) {
                      case 'INCOMING':
                          typeBadge = 'bg-green-100 text-green-800';
                          break;
                      case 'POS_SALE':
                          typeBadge = 'bg-red-100 text-red-800';
                          break;
                      case 'POS_CANCEL':
                          typeBadge = 'bg-yellow-100 text-yellow-800';
                          break;
                      case 'SALE_RETURN':
                          typeBadge = 'bg-blue-100 text-blue-800';
                          break;
                      case 'OPNAME':
                          typeBadge = 'bg-purple-100 text-purple-800';
                          break;
                      case 'PURCHASE_RETURN':
                          typeBadge = 'bg-orange-100 text-orange-800';
                          break;
                      default:
                          typeBadge = 'bg-gray-100 text-gray-800';
                  }
                  
                  html += `
                      <tr class="border-b">
                          <td class="px-4 py-2">${new Date(movement.moved_at).toLocaleTimeString('id-ID')}</td>
                          <td class="px-4 py-2"><span class="px-2 py-1 rounded text-xs ${typeBadge}">${typeText}</span></td>
                          <td class="px-4 py-2">${movement.ref_code || '-'}</td>
                          <td class="px-4 py-2">${movement.initial_qty}</td>
                          <td class="px-4 py-2 text-green-600">${movement.qty_in > 0 ? '+' + movement.qty_in : '-'}</td>
                          <td class="px-4 py-2 text-red-600">${movement.qty_out > 0 ? '-' + movement.qty_out : '-'}</td>
                          <td class="px-4 py-2 font-semibold">${movement.final_qty}</td>
                          <td class="px-4 py-2">${movement.user?.username || '-'}</td>
                          <td class="px-4 py-2">${movement.notes || '-'}</td>
                      </tr>
                  `;
              });
              
              html += `</tbody></table></div>`;
              document.getElementById('modalContent').innerHTML = html;
              document.getElementById('modalTitle').textContent = `Detail Pergerakan: ${data.product}`;
          })
          .catch(error => {
              console.error('Error:', error);
              document.getElementById('modalContent').innerHTML = '<div class="text-center py-8 text-red-600">Error loading data</div>';
          });
  }

  function closeModal() {
      document.getElementById('movementModal').classList.add('hidden');
  }

  // Close modal when clicking outside
  document.getElementById('movementModal').addEventListener('click', function(e) {
      if (e.target.id === 'movementModal') {
          closeModal();
      }
  });

  // Set default dates to today when page loads
  document.addEventListener('DOMContentLoaded', function() {
      const today = new Date().toISOString().split('T')[0];
      const fromInput = document.querySelector('input[name="from"]');
      const toInput = document.querySelector('input[name="to"]');
      
      if (fromInput && !fromInput.value) {
          fromInput.value = today;
      }
      if (toInput && !toInput.value) {
          toInput.value = today;
      }
  });
  </script>
</body>
</html>