<html>
  <head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <style>
    body {
      text-align: center;
      padding: 40px 0;
      background-color: #0a141b;
      color: white;
      margin: 0;
      font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
    }

    .card {
      background-color: #06324e;
      padding: 50px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
      display: inline-block;
      margin: 0 auto;
      max-width: 400px;
    }

    h1, h2, h3 {
      font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
      color: yellow;
      margin-bottom: 10px;
    }

    h1 {
      font-weight: 900;
      font-size: 28px;
    }

    h2 {
      font-weight: 700;
      font-size: 24px;
    }

    h3 {
      font-weight: 600;
      font-size: 20px;
      margin-bottom: 40px;
    }

    p {
      color: #b0b8c1;
      font-size: 16px;
      margin-bottom: 40px;
    }

    .loader {
      border: 6px solid transparent;
      border-top: 6px solid yellow;
      border-radius: 50%;
      width: 80px;
      height: 80px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .button {
      background-color: yellow;
      color: #06324e;
      border: none;
      padding: 10px 30px;
      font-size: 16px;
      font-weight: 700;
      border-radius: 4px;
      text-decoration: none;
      display: inline-block;
      transition: background-color 0.3s;
    }

    .button:hover {
      background-color: #ffcc00;
    }

    .logo {
      width: 150px;
      margin-bottom: 30px;
    }
  </style>
  <body>
    <div class="card">
      <img class="logo" src="assets/images/payments/sadapay.png" alt="SadaPay Logo">
      <h3>Thank You For Choosing Us</h3>
      <div class="loader"></div>
      <h2>SadaPay</h2>
      <h1>Request Received</h1>
      <p>Your transaction details have been received. Payment will be credited to your account within 1 hour after verifying your transaction.</p>
      <h1>THANK YOU</h1>

      <a href="/statistics" class="button">Dashboard</a>
    </div>
  </body>
</html>
