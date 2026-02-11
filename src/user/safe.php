<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/stepper.php';
$userId = $_SESSION["user_id"];

// Get user's full name from applications table
$stmtApp = $conn->prepare("SELECT surname, firstname, mi FROM applications WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmtApp->bind_param("i", $userId);
$stmtApp->execute();
$appData = $stmtApp->get_result()->fetch_assoc();
$stmtApp->close();

$fullName = "";
if ($appData) {
  $fullName = strtoupper(trim($appData["firstname"] . " " . ($appData["mi"] ? $appData["mi"] . ". " : "") . $appData["surname"]));
}

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Safe From Harm • BSP</title>
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
    <div class="text-center mb-4">
      <h1 class="text-2xl font-bold text-green-800 mb-1">Safe From Harm Policy</h1>
      <p class="text-sm text-gray-600">Review and agree before proceeding to payment.</p>
    </div>

    <!-- Progress Stepper -->
    <?php render_stepper(2); ?>

    <!-- Policy Card -->
    <div class="card bg-white shadow-xl mt-6 border border-gray-200">
      <div class="card-body p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">Policy Statement</h2>
          <div class="badge badge-success text-sm px-3 py-2">Step 2</div>
        </div>

        <!-- Policy Content Box - Matching Screenshot Format -->
        <div class="border-2 border-green-700 bg-green-50 p-8 mb-6 rounded-xl shadow-sm">
          <h3 class="text-center text-lg font-bold mb-4 uppercase text-green-800 tracking-wide border-b border-green-700 pb-2">Safe From Harm</h3>
          <div class="text-[15px] leading-relaxed text-justify text-gray-800 space-y-4">
            <p>
              World Scouting emphasizes that the achievement of Scouting's Mission makes it essential for the Movement to provide young people
              with a <span class="font-semibold">"safe passage"</span> based on respect for their integrity and their right to develop in a non-constraining environment. The Boy Scouts of
              the Philippines implements <span class="font-semibold">"Safe from Harm"</span> on the conviction that all adults and children have a right NOT to be abused. This is a
              fundamental human right. Abuse can take the form of bullying, physical abuse, emotional abuse, neglect, sexual abuse and exploitation. It
              is important to note that young people can suffer from one or a combination of these forms of abuse. Abuse can take place at home, at
              school or anywhere young people spend time. In the great majority of cases, the abuser is someone the young person knows, such as a
              parent, teacher, relative, leader or friend. The main objective is to ensure that no one will be exposed to abuse. Good child protection
              practice means making sure that everyone is aware of signs of potential abuse. It is based upon the Declaration on the Rights of the Child
              and Human Rights.
            </p>
            <p class="font-medium">
              I hereby commit and fully subscribe to the existing Safe From Harm Policy of the Boy Scouts of the Philippines, and that I hereby
              absolve and free the BSP from any liability arising from any of my acts contrary to the policy. I hereby accept that the BSP may
              immediately revoke my registration as an adult leader upon violation of such policy.
            </p>
            <!-- Signature Section -->
            <div class="mt-8 pt-4">
              <div class="text-center border-green-700 pt-2">
                <p class="font-bold text-base mb-1 text-green-900 tracking-wide"><?php echo htmlspecialchars($fullName); ?></p>
                <p class="text-xs text-gray-700 border-t w-1/2 mx-auto">Signature Over Printed Name</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Agreement Section -->
        <div class="bg-gray-50 border-2 border-green-200 rounded-lg p-4 mb-6">
          <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3 p-0">
              <input id="safe-agree" type="checkbox" class="checkbox checkbox-success" />
              <span class="label-text text-sm font-semibold text-gray-800">I have read and agree to the Safe From Harm Policy.</span>
            </label>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row justify-between gap-3">
          <a href="step1.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Part 1
          </a>
          <button id="safe-next" class="btn btn-success btn-sm text-white" type="button" data-href="payment.php" disabled>
            Proceed to Payment
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="../../public/assets/js/app.js"></script>
</body>

</html>