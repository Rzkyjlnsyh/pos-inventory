@foreach ($notifications as $notification)
    <div class="bg-gray-50 p-4 rounded-lg mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div>
                    <p class="text-gray-800">{{ $notification->message }}</p>
                    <p class="text-gray-500 text-sm">{{ $notification->created_at->format('h:i A') }}</p>
                </div>
            </div>
            <div class="text-gray-500 text-sm">
                <div class="flex items-center">
                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                    {{ $notification->created_at->format('F j, Y') }}
                </div>
            </div>
        </div>
    </div>
@endforeach