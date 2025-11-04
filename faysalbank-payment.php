<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8 max-w-md w-full text-center">
      <img src="https://beastsmm.pk/assets/images/payments/faysal-bank.png" alt="JazzCash Logo" class="mx-auto w-24 mb-4">
      <h3 class="text-yellow-400 font-semibold text-xl">Thanks For Choosing Us</h3>
      <div class="loader border-4 border-yellow-400 border-t-transparent rounded-full w-16 h-16 mx-auto my-4 animate-spin"></div>
      <h2 class="text-yellow-400 font-extrabold text-3xl"Faysal Bank</h2>
      <h1 class="text-yellow-400 font-extrabold text-2xl mt-4">Request Received</h1>
      <p class="text-gray-300 text-lg mt-4">Your transaction details have been received,<br/>payment will be credited to your account<br/>within 1 hour after verifying your transaction.</p>
      <h1 class="text-yellow-400 font-extrabold text-2xl mt-6">THANK YOU</h1>
      <a href="/order/add" class="mt-6 inline-block bg-yellow-400 text-gray-900 font-semibold py-3 px-6 rounded-lg hover:bg-yellow-500 transition">New Order</a>
    </div>
    <style>
      .loader {
        display: inline-block;
        border: 4px solid transparent;
        border-top-color: #facc15;
        border-radius: 50%;
        width: 64px;
        height: 64px;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        to {
          transform: rotate(360deg);
        }
      }
    </style>
  </body>
</html>
