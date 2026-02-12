<?php
session_start();
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in to BSP Portal</title>
  <link rel="stylesheet" href="assets/css/output.css" />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic" rel="stylesheet" />
</head>

<body class="min-h-screen overflow-hidden" style="font-family: 'Montserrat', sans-serif;">
  <!-- Split Screen Layout -->
  <div class="flex min-h-screen">
    <!-- Left Side - Image -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-linear-to-br from-green-800 to-green-600 select-none">
      <img
        src="assets/images/boyscout.jpg"
        alt="Boy Scouts of the Philippines"
        class="absolute inset-0 w-full h-full object-cover opacity-80 pointer-events-none" />
      <div class="absolute inset-0 bg-linear-to-br from-green-900/60 to-green-700/40"></div>
      <div class="absolute left-0 bottom-0 z-10 p-0">
        <h1 class="text-6xl font-bold text-white tracking-normal">BSP.NAV</h1>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-base-100">
      <div class="w-full max-w-md space-y-8">
        <!-- Logo -->
        <div class="flex justify-center">
          <img
            src="assets/images/NCC-BSP.png"
            alt="BSP Logo"
            class="h-24 w-24" />
        </div>

        <!-- Heading -->
        <div class="text-center">
          <h2 class="text-2xl font-bold text-gray-900">Sign in to your BSP Portal</h2>
        </div>

        <!-- Login Form -->
        <form id="login-form" method="post" action="../src/user/login_process.php" class="space-y-6">
          <?php if ($error === "notfound") : ?>
            <div class="alert alert-error shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" /></svg>
              <div class="text-sm">Account does not exist.</div>
            </div>
          <?php elseif ($error === "invalid") : ?>
            <div class="alert alert-error shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" /></svg>
              <div class="text-sm">Incorrect password.</div>
            </div>
          <?php elseif ($error === "blocked") : ?>
            <?php
              $reason = $_GET["reason"] ?? "Account blocked.";
              $start = $_GET["start"] ?? null;
              $end = $_GET["end"] ?? null;
              $message = $reason;
              if ($start && $end) {
                $message .= "\nBlocked from: " . date("M d, Y H:i", (int)$start) . " to " . date("M d, Y H:i", (int)$end);
              }
            ?>
            <div class="alert alert-error shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" /></svg>
              <div class="text-sm"><?php echo nl2br(htmlspecialchars($message)); ?></div>
            </div>
          <?php endif; ?>
          <!-- Username Field -->
          <div class="form-control">
            <label class="label" for="login-username">
              <span class="label-text text-base font-medium text-gray-700">Username</span>
            </label>
            <input
              id="login-username"
              name="username"
              type="text"
              placeholder="Enter your username"
              class="input input-bordered w-full focus:input-primary"
              required />
          </div>

          <!-- Password Field -->
          <div class="form-control">
            <label class="label" for="login-password">
              <span class="label-text text-base font-medium text-gray-700">Password</span>
            </label>
            <input
              id="login-password"
              name="password"
              type="password"
              placeholder="Enter your password"
              class="input input-bordered w-full focus:input-primary"
              required />
          </div>

          <!-- Remember Me -->
          <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
              <input type="checkbox" class="checkbox checkbox-sm" />
              <span class="label-text text-gray-600">Remember me</span>
            </label>
          </div>

          <!-- Login Button -->
          <button type="submit" class="btn btn-primary w-full bg-green-700 hover:bg-green-800 border-0 text-white normal-case text-base">
            LOG IN
          </button>

          <!-- Links Row -->
          <div class="flex justify-between items-center text-sm">
            <button type="button" class="link link-hover text-gray-600 no-underline hover:text-green-700">
              Forgot password?
            </button>
            <a href="../src/user/signup.php" class="link link-hover text-green-700 no-underline hover:text-green-800 font-medium">
              Create account
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="js/app.js"></script>
</body>

</html>