<!DOCTYPE html>
<html>
<head>
    <title>Print Closing Summary</title>
    <script>
        window.onload = function() {
            // Auto print ketika halaman loaded
            window.print();
            
            // Redirect kembali setelah print (atau cancel)
            setTimeout(function() {
                window.location.href = "{{ route('owner.shift.history') }}";
            }, 1000);
        };
    </script>
</head>
<body>
    <!-- Sertakan view closing summary yang sama -->
    @include('owner.shift.closing_summary')
</body>
</html>