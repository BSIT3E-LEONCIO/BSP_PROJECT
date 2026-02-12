<?php
session_start();
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Portal • BSP</title>
  <link rel="stylesheet" href="assets/css/output.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
    }
  </style>
</head>

<body class="min-h-screen flex items-center justify-center p-6">
  <div class="w-full max-w-md">

    <!-- Header with Logo -->
    <div class="text-center mb-6">
      <div class="flex justify-center mb-2">
        <img src="../public/assets/images/NCC-BSP.png" alt="BSP Navotas Logo" class="h-16 w-16 rounded-full shadow-md border-2 border-green-700 bg-white object-contain" />
      </div>
      <h1 class="text-xl font-bold text-white mb-1">Admin Portal</h1>
      <p class="text-green-100 text-xs">BSP Management Access</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-xl shadow-2xl p-5">
      <div class="flex justify-center mb-4">
        <span class="badge badge-success px-3 py-1 text-xs font-semibold">ADMINISTRATOR</span>
      </div>

      <?php if ($error === "notfound") : ?>
        <div class="alert alert-error shadow-lg mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" /></svg>
          <span>Admin account not found.</span>
        </div>
      <?php elseif ($error === "invalid") : ?>
        <div class="alert alert-error shadow-lg mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" /></svg>
          <span>Incorrect password.</span>
        </div>
      <?php endif; ?>
      <form class="space-y-3" method="post" action="../src/admin/admin_login_process.php">
        <!-- Username -->
        <div class="form-control">
          <label class="label pb-1">
            <span class="label-text font-medium text-gray-700 text-sm">Username</span>
          </label>
          <div class="relative">
            <input
              id="admin-username"
              name="username"
              type="text"
              placeholder="Enter admin username"
              class="input input-sm input-bordered w-full pl-9 focus:border-green-500"
              required />
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>

        <!-- Password -->
        <div class="form-control">
          <label class="label pb-1">
            <span class="label-text font-medium text-gray-700 text-sm">Password</span>
          </label>
          <div class="relative">
            <input
              id="admin-password"
              name="password"
              type="password"
              placeholder="Enter password"
              class="input input-sm input-bordered w-full pl-9 focus:border-green-500"
              required />
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
        </div>

        <!-- Buttons -->
        <div class="space-y-2 pt-2">
          <button class="btn btn-success btn-sm w-full text-white font-semibold" type="submit">
            Login to Dashboard
          </button>
          <a class="btn btn-outline btn-success btn-sm w-full" href="index.php">
            Back to User Portal
          </a>
        </div>
      </form>

    </div>

    <!-- Error Messages moved above form for visibility -->
</body>

</html>