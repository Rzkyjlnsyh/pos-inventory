<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Login - BbIarA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Loader styles */
    .loader {
      width: 80px;
      height: 40px;
      background:
        radial-gradient(circle 25px at top right, #ffd738 40%,#0000 ),
        #4dbefa;
      position: relative;
      overflow: hidden;
    }
    .loader:before,
    .loader:after{
      content: "";
      position: absolute;
      top: 4px;
      left: -40px;
      width: 36px;
      height: 20px;
      --c: radial-gradient(farthest-side,#fff 96%,#0000);
      background: 
        var(--c) 100% 100% /30% 60%, 
        var(--c) 70% 0 /50% 100%, 
        var(--c) 0 100% /36% 68%, 
        var(--c) 27% 18% /26% 40%,
        linear-gradient(#fff 0 0) bottom/67% 58%;
      background-repeat: no-repeat;
      animation: l10 2s linear infinite;
    }
    .loader:after {
      top:15px;
      --l:200%;
    }
    @keyframes l10{
      100% {left:var(--l,105%)}
    }

    /* Loading screen styles with fade effect */
    .loading-screen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgb(243 244 246);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      opacity: 1;
      transition: opacity 1s ease-in-out;
    }

    .loading-screen.fade-out {
      opacity: 0;
    }

    /* Hide content initially */
    .content {
      display: none;
      opacity: 0;
      transition: opacity 1s ease-in-out;
    }

    .content.fade-in {
      opacity: 1;
    }
  </style>
</head>
<body>
  <!-- Loading Screen -->
  <div class="loading-screen">
    <div class="loader"></div>
  </div>

  <!-- Main Content -->
  <div class="content flex items-center justify-center min-h-screen bg-gray-100">
    <!-- Your existing content here -->
    <div class="p-8 rounded-lg w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-6">
        <img src="{{ asset('assets/logo-transparent.png') }}" alt="BbIarA Logo" class="w-48 mx-auto">
      </div>

      @auth
      <!-- Authenticated User Message -->
      <div class="text-center mb-6">
        <p class="text-lg font-medium text-gray-700">You are already logged in.</p>
        @if(Auth::user()->usertype == 'owner')
          <a href="{{ route('owner.dashboard.index') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005281] hover:bg-[#153c53] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Go to Dashboard
          </a>
        @elseif(Auth::user()->usertype == 'karyawan')
          <a href="{{ route('karyawan.cashier.index') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005281] hover:bg-[#153c53] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Go to Cashier
          </a>
        @elseif(Auth::user()->usertype == 'inventaris')
          <a href="{{ route('inventaris.stock.index') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005281] hover:bg-[#153c53] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Go to Stock Management
          </a>
        @endif
      </div>
      @else
      <!-- Session Status -->
      @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">
          {{ session('status') }}
        </div>
      @endif

      <!-- Form -->
      <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email*</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" 
                 class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                 required autofocus>
          @error('email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password*</label>
          <input type="password" id="password" name="password" placeholder="minimum 8 characters" 
                 class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                 required>
          @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- Remember me and Forgot password -->
        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <input type="checkbox" name="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
          </label>
        </div>

        <!-- Submit Button -->
        <div>
          <button type="submit" 
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#005281] hover:bg-[#153c53] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Login
          </button>
        </div>
      </form>
      @endauth
    </div>
  </div>

  <script>
    // Function to handle the loading animation and transitions
    function handleLoadingTransition() {
      const loadingScreen = document.querySelector('.loading-screen');
      const content = document.querySelector('.content');

      // Show loading screen for 3 seconds
      setTimeout(() => {
        // Add fade-out class to loading screen
        loadingScreen.classList.add('fade-out');
        
        // Display content but keep it invisible
        content.style.display = 'flex';
        
        // Force a reflow to ensure the transition works
        void content.offsetWidth;
        
        // Add fade-in class to content
        content.classList.add('fade-in');

        // Remove loading screen after fade out animation completes
        setTimeout(() => {
          loadingScreen.style.display = 'none';
        }, 1000); // Match this with the CSS transition duration
      }, 3000); // Loading screen duration (3 seconds)
    }

    // Start the loading sequence when the page loads
    window.addEventListener('load', handleLoadingTransition);

    // Optional: Add loading screen when navigating away
    window.addEventListener('beforeunload', () => {
      const loadingScreen = document.querySelector('.loading-screen');
      loadingScreen.style.display = 'flex';
      loadingScreen.classList.remove('fade-out');
    });
  </script>
</body>
</html>