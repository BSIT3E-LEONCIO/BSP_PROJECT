<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];

$appStmt = $conn->prepare("SELECT status, created_at, serve_sub FROM applications WHERE user_id = ?");
$appStmt->bind_param("i", $userId);
$appStmt->execute();
$appResult = $appStmt->get_result();
$application = $appResult->fetch_assoc();
$appStmt->close();

$payStmt = $conn->prepare("SELECT status, uploaded_at FROM payments WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 1");
$payStmt->bind_param("i", $userId);
$payStmt->execute();
$payResult = $payStmt->get_result();
$payment = $payResult->fetch_assoc();
$payStmt->close();

$appStatus = $application["status"] ?? "not-submitted";
$paymentStatus = $payment["status"] ?? "not-submitted";
$paymentTime = $payment["uploaded_at"] ?? null;
$serveSub = $application["serve_sub"] ?? null;

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

if ($appStatus === "approved" && $paymentStatus === "approved" && $serveSub) {
  $roleMap = [
    "Langkay Leader/Assistant" => "../roles/role_langkay.php",
    "Kawan Leader/Assistant" => "../roles/role_kawan.php",
    "Troop Leader/Assistant" => "../roles/role_troop.php",
    "Outfit Leader/Assistant" => "../roles/role_outfit.php",
    "Circle Manager/Assistant" => "../roles/role_circle.php",
    "Institutional Scouting Representative/ISCOM/ISC" => "../roles/role_iscom.php",
    "District/Municipal Commissioner/Coordinator/Member-at-Large" => "../roles/role_district.php",
    "Local Council" => "../roles/role_local_council.php"
  ];
  if (isset($roleMap[$serveSub])) {
    header("Location: " . $roleMap[$serveSub]);
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application Status • BSP</title>
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
  <?php if ($blockedActive): ?>
    <script>
      document.body.classList.add('blocked');
    </script>
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-9999 flex items-center justify-center">
      <div class="bg-white rounded-xl shadow-2xl p-10 max-w-md text-center">
        <h2 class="text-2xl font-bold text-red-700 mb-4">Account Blocked</h2>
        <p class="text-gray-700 mb-3">Your account has been blocked by the administrator.</p>
        <p class="text-gray-700 mb-3"><strong>Reason:</strong> <?php echo htmlspecialchars($blocked["blocked_reason"] ?? "Blocked by administrator."); ?></p>
        <p class="text-gray-700"><strong>Blocked Period:</strong><br><?php echo htmlspecialchars($blocked["blocked_start"]); ?> to <?php echo htmlspecialchars($blocked["blocked_end"]); ?></p>
      </div>
    </div>
  <?php endif; ?>

  <div class="max-w-5xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-2xl font-bold text-green-800 mb-1">Application Status</h1>
      <p class="text-sm text-gray-600">Check your Adult Registration and payment verification status.</p>
    </div>

    <!-- Status Card -->
    <div class="card bg-white shadow-xl border border-gray-200">
      <div class="card-body p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">Status Overview</h2>
          <div class="badge badge-success text-sm px-3 py-2">Dashboard</div>
        </div>

        <!-- Status Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Application Status -->
          <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
            <div class="flex items-center mb-4">
              <div class="bg-green-600 rounded-full p-3 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-white w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700 block">AAR Application</label>
                <div class="badge <?php
                                  echo $appStatus === 'approved' ? 'badge-success' : ($appStatus === 'pending' ? 'badge-warning' : ($appStatus === 'rejected' ? 'badge-error' : 'badge-ghost'));
                                  ?> badge-sm mt-1">
                  <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $appStatus))); ?>
                </div>
              </div>
            </div>
            <div class="text-sm text-gray-700">
              <?php if ($appStatus === "pending") : ?>
                <p>Your AAR form is currently under review by the administrator.</p>
              <?php elseif ($appStatus === "approved") : ?>
                <p class="font-semibold text-green-700">✓ Your AAR form has been approved!</p>
              <?php elseif ($appStatus === "rejected") : ?>
                <p class="text-red-700">Your AAR form needs correction. Please contact the council.</p>
              <?php else : ?>
                <p>Please complete the application form to proceed.</p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Payment Status -->
          <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6">
            <div class="flex items-center mb-4">
              <div class="bg-green-600 rounded-full p-3 mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-white w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700 block">Payment Verification</label>
                <div class="badge <?php
                                  echo $paymentStatus === 'approved' ? 'badge-success' : ($paymentStatus === 'pending' ? 'badge-warning' : ($paymentStatus === 'rejected' ? 'badge-error' : 'badge-ghost'));
                                  ?> badge-sm mt-1">
                  <?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $paymentStatus))); ?>
                </div>
              </div>
            </div>
            <div class="text-sm text-gray-700">
              <?php if ($paymentStatus === "pending") : ?>
                <p>Payment proof submitted. Verification is pending.</p>
                <?php if ($paymentTime) : ?>
                  <p class="text-xs text-gray-500 mt-1">Submitted: <?php echo htmlspecialchars($paymentTime); ?></p>
                <?php endif; ?>
              <?php elseif ($paymentStatus === "approved") : ?>
                <p class="font-semibold text-green-700">✓ Payment has been verified!</p>
              <?php elseif ($paymentStatus === "rejected") : ?>
                <p class="text-red-700">Payment rejected. Please upload again.</p>
              <?php else : ?>
                <p>No payment submitted yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php if ($appStatus === "not-submitted") : ?>
            <a class="btn btn-success text-white" href="step1.php">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Start Application
            </a>
          <?php else : ?>
            <a class="btn btn-outline btn-success" href="step1.php">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              View/Update Application
            </a>
          <?php endif; ?>

          <?php if ($paymentStatus === "not-submitted") : ?>
            <a class="btn btn-success text-white" href="payment.php">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              Submit Payment
            </a>
          <?php elseif ($paymentStatus === "rejected") : ?>
            <a class="btn btn-outline btn-warning" href="payment.php">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Update Payment
            </a>
          <?php else : ?>
            <a class="btn btn-outline btn-info" href="wait.php">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              View Status Timer
            </a>
          <?php endif; ?>

          <a class="btn btn-outline btn-error" href="logout.php">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>