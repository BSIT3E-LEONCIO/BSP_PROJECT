<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/stepper.php';

$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT serve_main FROM applications WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

$serveMain = $app["serve_main"] ?? "";
$amount = 0;
$qrImage = null;
if ($serveMain === "unit") {
  $amount = 60;
  $qrImage = "../../public/assets/qr_60.png";
} elseif ($serveMain === "lay") {
  $amount = 100;
  $qrImage = "../../public/assets/qr_100.png";
}
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
$qrImagePath = $qrImage ? __DIR__ . "/../../public/assets/" . basename($qrImage) : null;
$qrExists = $qrImagePath && file_exists($qrImagePath);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mode of Payment • BSP</title>
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

  <div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="text-center mb-4">
      <h1 class="text-2xl font-bold text-green-800 mb-1">Mode of Payment</h1>
      <p class="text-sm text-gray-600">Upload your payment proof to begin verification.</p>
    </div>

    <!-- Progress Stepper -->
    <?php render_stepper(3); ?>

    <!-- Payment Card -->
    <div class="card bg-white shadow-lg mt-6">
      <div class="card-body p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-800">Payment Details</h2>
          <span class="badge badge-success text-sm px-3 py-2">Step 3</span>
        </div>

        <!-- QR Code Section -->
        <div class="bg-green-50 border-2 border-green-600 rounded-xl p-6 mb-6">
          <div class="flex items-start gap-4 mb-4">
            <div class="bg-green-600 rounded-full p-3 shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-white w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-xl font-bold text-green-800 mb-2">Scan QR Code to Pay</h3>
              <?php if ($serveMain === "unit" || $serveMain === "lay") : ?>
                <p class="text-sm text-gray-700 mb-4">Use your preferred e-wallet to scan the QR code and complete your payment.</p>
                <div class="bg-white rounded-lg p-4 border border-green-200 mb-4">
                  <p class="text-3xl font-bold text-green-700 text-center">PHP <?php echo number_format($amount, 2); ?></p>
                  <p class="text-xs text-gray-600 text-center mt-1">Amount Due</p>
                </div>
                <?php if ($qrExists) : ?>
                  <div class="flex justify-center">
                    <img src="<?php echo htmlspecialchars($qrImage); ?>" alt="Payment QR Code" class="max-w-xs border-4 border-green-600 rounded-xl shadow-lg bg-white p-2" />
                  </div>btn-primary
                <?php else : ?>
                  <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>QR code image is not available.</span>
                  </div>
                <?php endif; ?>
              <?php else : ?>
                <p class="text-sm text-gray-700">Please complete the application to see your payment QR code.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="divider text-gray-600 font-semibold">Upload Payment Proof</div>

        <!-- Upload Form -->
        <form id="payment-form" method="post" action="payment_upload.php" enctype="multipart/form-data">

          <div class="bg-gray-50 border-2 border-green-200 rounded-xl p-6 mb-6">
            <div class="form-control mb-4">
              <label class="label">
                <span class="label-text font-semibold text-gray-800">Payment Proof (Image)</span>
              </label>
              <input
                id="payment-proof"
                name="payment_proof"
                type="file"
                accept="image/*"
                class="file-input file-input-bordered file-input-success w-full bg-white"
                required />
              <label class="label">
                <span class="label-text-alt text-gray-600">Accepted formats: JPG, PNG, GIF</span>
              </label>
            </div>

            <!-- Image Preview -->
            <div id="upload-preview-container" class="hidden">
              <div class="bg-green-50 border border-green-300 rounded-lg p-4">
                <div class="flex items-center mb-3">
                  <div class="bg-green-600 rounded-full p-2 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-white w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <span class="font-semibold text-green-800">Preview of Your Payment Proof</span>
                </div>
                <div class="bg-white rounded-lg p-2 border-2 border-green-600">
                  <img id="upload-preview-img" src="#" alt="Payment Proof Preview" class="rounded-lg max-h-80 max-w-full mx-auto shadow-md" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row justify-between gap-4">
            <a href="safe.php" class="btn btn-outline btn-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Back to Safe From Harm
            </a>
            <button id="payment-next" class="btn btn-success btn-md text-white" type="submit" disabled>
              Submit Payment
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="../../public/js/app.js"></script>
  </div>
  <script>
    // Payment proof image preview
    const paymentProofInput = document.getElementById('payment-proof');
    const previewContainer = document.getElementById('upload-preview-container');
    const previewImg = document.getElementById('upload-preview-img');
    const submitBtn = document.getElementById('payment-next');

    paymentProofInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
          previewImg.src = ev.target.result;
          previewContainer.classList.remove('hidden');
          submitBtn.disabled = false;
        };
        reader.readAsDataURL(file);
      } else {
        previewImg.src = '#';
        previewContainer.classList.add('hidden');
        submitBtn.disabled = true;
      }
    });
  </script>
</body>

</html>