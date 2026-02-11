<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/stepper.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT UNIX_TIMESTAMP(uploaded_at) AS uploaded_ts FROM payments WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();
$stmt->close();

if (!$payment) {
  header("Location: payment.php");
  exit;
}

$startMs = ((int) $payment["uploaded_ts"]) * 1000;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verification in Progress • BSP</title>
  <link rel="stylesheet" href="../../public/assets/css/output.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }

    body.blocked {
      pointer-events: none;
      user-select: none;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen">
  <?php
  // Blocked user overlay logic
  $stmt = $conn->prepare("SELECT blocked_start, blocked_end, blocked_reason FROM users WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $blocked = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  $blockedActive = false;
  if ($blocked && $blocked["blocked_start"] && $blocked["blocked_end"]) {
    $today = date("Y-m-d");
    if ($today >= $blocked["blocked_start"] && $today <= $blocked["blocked_end"]) {
      $blockedActive = true;
    }
  }
  if ($blockedActive): ?>
    <script>
      document.body.classList.add('blocked');
    </script>
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9999] flex items-center justify-center">
      <div class="bg-white rounded-xl shadow-2xl p-10 max-w-md text-center">
        <h2 class="text-2xl font-bold text-red-700 mb-4">Account Blocked</h2>
        <p class="text-gray-700 mb-3">Your account has been blocked by the administrator.</p>
        <p class="text-gray-700 mb-3"><strong>Reason:</strong> <?php echo htmlspecialchars($blocked["blocked_reason"] ?? "Blocked by administrator."); ?></p>
        <p class="text-gray-700"><strong>Blocked Period:</strong><br><?php echo htmlspecialchars($blocked["blocked_start"]); ?> to <?php echo htmlspecialchars($blocked["blocked_end"]); ?></p>
      </div>
    </div>
  <?php endif; ?>

  <div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-green-800 mb-2">Verification in Progress</h1>
      <p class="text-gray-600">Please wait 24 hours for payment approval.</p>
    </div>

    <!-- Progress Stepper -->
    <?php render_stepper(5); ?>

    <!-- Timer Card -->
    <div class="card bg-white shadow-lg mt-8">
      <div class="card-body text-center">
        <div class="flex items-center justify-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-800">Waiting Timer</h2>
          <span class="badge badge-success ml-3">Finish</span>
        </div>

        <!-- Success Icon -->
        <div class="flex justify-center mb-6">
          <div class="rounded-full bg-green-100 p-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>

        <!-- Timer Display -->
        <div class="mb-6">
          <p class="text-6xl font-bold text-green-700 mb-4" id="timer" data-start-ms="<?php echo htmlspecialchars((string) $startMs); ?>">24:00:00</p>
          <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span id="timer-message">We will notify you once verification is complete.</span>
          </div>
        </div>

        <div class="flex justify-center gap-4">
          <button id="timer-login" class="btn bg-green-700 hover:bg-green-800 text-white" type="button">
            Back to Login
          </button>
          <a class="btn btn-outline" href="status.php">Check Status</a>
        </div>
      </div>
    </div>

    <!-- Additional Info -->
    <div class="mt-6 text-center text-gray-500 text-sm">
      <p>Your payment is being verified by our admin team.</p>
      <p>This process typically takes up to 24 hours.</p>
    </div>
  </div>
  <script src="../../public/assets/js/app.js"></script>
</body>

</html>