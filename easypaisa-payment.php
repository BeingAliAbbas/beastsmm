<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-gray-900 text-gray-300 min-h-screen flex items-center justify-center">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8 max-w-lg text-center">
      <div class="flex justify-center mb-4">
        <img src="https://beastsmm.pk/assets/images/payments/easypaise.png" alt="EasyPaisa Logo" class="w-24 h-auto">
      </div>
      <h3 class="text-lg font-semibold text-yellow-400 mb-2">Thank You For Choosing Us</h3>
      <div class="loader border-4 border-yellow-400 border-t-transparent rounded-full w-16 h-16 mx-auto animate-spin mb-6"></div>
      <h2 class="text-2xl font-bold text-yellow-400">EasyPaisa</h2>
      <h1 class="text-xl font-extrabold text-yellow-400 my-4">Request Received</h1>
      <p class="text-gray-300 text-base leading-6">
        Your transaction details have been received.<br />
        Payment will be credited to your account<br />
        within 1 hour after verifying your transaction.
      </p>
      <h1 class="text-xl font-extrabold text-yellow-400 my-4">THANK YOU</h1>
      <a href="/order/add" class="inline-block bg-yellow-400 text-gray-800 font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-yellow-500 transition">New Order</a>
    </div>
    <style>
      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }
        100% {
          transform: rotate(360deg);
        }
      }
    </style>
  </body>
</html>
