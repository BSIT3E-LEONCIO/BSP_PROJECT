<?php
session_start();
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account • BSP Portal</title>
  <link rel="stylesheet" href="../../public/assets/css/output.css" />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic" rel="stylesheet" />
</head>

<body class="min-h-screen bg-gray-50" style="font-family: 'Montserrat', sans-serif;">
  <div class="max-w-4xl mx-auto py-6 px-4">
    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-1">AAR Account Creation</h1>
      <p class="text-sm text-gray-600">Follow the steps to complete your Adult Registration.</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
      <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-2">Account Details</h2>
        <p class="text-sm text-gray-600">Create your account to begin registration</p>
      </div>

      <form id="signup-form" method="post" action="signup_process.php" class="space-y-6">
        <!-- Email -->
        <div class="form-control">
          <label class="label" for="signup-email">
            <span class="label-text text-base font-medium text-gray-700">Email</span>
          </label>
          <input
            id="signup-email"
            name="email"
            type="email"
            placeholder="name@example.com"
            class="input input-bordered w-full focus:input-primary"
            required />
        </div>

        <!-- Username -->
        <div class="form-control">
          <label class="label" for="signup-username">
            <span class="label-text text-base font-medium text-gray-700">Username</span>
          </label>
          <input
            id="signup-username"
            name="username"
            type="text"
            placeholder="Choose a username"
            class="input input-bordered w-full focus:input-primary"
            required />
        </div>

        <!-- Password -->
        <div class="form-control">
          <label class="label" for="signup-password">
            <span class="label-text text-base font-medium text-gray-700">Password</span>
          </label>
          <input
            id="signup-password"
            name="password"
            type="password"
            placeholder="Create a password"
            class="input input-bordered w-full focus:input-primary"
            required />
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 pt-4">
          <a href="../../public/index.php" class="btn btn-outline flex-1">
            Back to Login
          </a>
          <button type="submit" class="btn bg-green-700 hover:bg-green-800 text-white border-0 flex-1">
            Next
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="../../public/assets/js/app.js"></script>
  <?php if ($error === "exists") : ?>
    <script>
      alert("Account already exists.");
    </script>
  <?php endif; ?>
</body>

</html>