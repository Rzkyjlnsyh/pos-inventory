<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock Opname - Custom Pare</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body{ font-family: 'Nunito,sans-serif'; }
  </style>
</head>
<body class="bg-gray-100">
  <div class="flex-1">
    <div class="p-4 lg:p-8">
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Stock Opname</h3>
          <a href="{{ route('owner.inventory.stock-opnames.create') }}" class="bg-[#005281] text-white px-4 py-2 rounded-lg hover:bg-[#00446a]">
            <i class="bi bi-plus-circle"></i> Tambah Opname
          </a>
        </div>

        @if(session('success'))
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
          </div>
        @endif

        <div class="overflow-x-auto">
          <table class="min-w-full border rounded-lg">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2">No Dokumen</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Catatan</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Dibuat oleh</th>
                <th class="px-4 py-2">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($stockOpnames as $opname)
              <tr class="border-b">
                <td class="px-4 py-2">{{ $opname->document_number }}</td>
                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($opname->date)->format('d M Y') }}</td>
                <td class="px-4 py-2">{{ $opname->notes ?? '-' }}</td>
                <td class="px-4 py-2">
                <span class="px-2 py-1 rounded-full text-xs 
    {{ $opname->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
    {{ ucfirst($opname->status) }}
</span>
                </td>
                <td class="px-4 py-2">{{ $opname->creator_label }}</td>
                <td class="px-4 py-2">
                  <div class="flex space-x-2">
                    <!-- Tombol Detail -->
                    <a href="{{ route('owner.inventory.stock-opnames.show', $opname->id) }}" 
       class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
       <i class="bi bi-eye"></i>
    </a>
    
                    
                    <!-- Tombol Approve (Hanya untuk Status Draft Saja) -->
                    @if($opname->status === 'draft')
      <form action="{{ route('owner.inventory.stock-opnames.approve', $opname->id) }}" method="POST">
        @csrf
        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
          <i class="bi bi-check-circle"></i>
        </button>
      </form>
    @endif
                    
    <!-- Tombol Hapus Hilang (Ketika Status sudah di Approve) -->
    @if($opname->status === 'draft')
      <form action="{{ route('owner.inventory.stock-opnames.destroy', $opname->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
          onclick="return confirm('Apakah Anda yakin ingin menghapus stock opname ini?')">
          <i class="bi bi-trash"></i>
        </button>
      </form>
    @endif
</div>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $stockOpnames->links() }}
        </div>
      </div>
    </div>
  </div>
</body>
</html>