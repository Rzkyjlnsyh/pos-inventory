<!DOCTYPE html>
   <html lang="en">
   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Dashboard - Pare Custom</title>
       <script src="https://cdn.tailwindcss.com"></script>
       <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
       <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
       <style>
           body { font-family: 'Raleway', sans-serif; }
       </style>
   </head>
   <body class="bg-gray-100">
       <div class="flex">
           @if (Auth::user()->usertype === 'admin')
               <x-navbar-admin></x-navbar-admin>
           @elseif (Auth::user()->usertype === 'owner')
               <x-navbar-owner></x-navbar-owner>
           @elseif (Auth::user()->usertype === 'editor')
               <x-navbar-editor></x-navbar-editor>
           @elseif (Auth::user()->usertype === 'finance')
               <x-navbar-finance></x-navbar-finance>
           @elseif (Auth::user()->usertype === 'kepala_toko')
               <x-navbar-kepala-toko></x-navbar-kepala-toko>
           @endif

           <div class="flex-1 p-4 lg:p-8">
               <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>
               <div class="bg-white p-6 rounded-lg shadow">
                   <p class="text-gray-700">Selamat datang, {{ Auth::user()->name }}!</p>
                   <p class="text-gray-700">Role: {{ Auth::user()->usertype }}</p>
                   @if (session('error'))
                       <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                           <span class="block sm:inline">{{ session('error') }}</span>
                       </div>
                   @endif
               </div>
           </div>
       </div>
   </body>
   </html>