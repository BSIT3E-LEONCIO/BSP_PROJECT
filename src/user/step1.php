<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/stepper.php';
if (!isset($_SESSION["user_id"])) {
  header("Location: ../../public/index.php");
  exit;
}

$userId = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT surname, firstname, mi, sex, civil_status, tenure, serve_main, serve_sub, sponsoring_institutions, council, dob, pob, religion, profession, position_title FROM applications WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$app = $result->fetch_assoc();
$stmt->close();

$isEditing = !empty($app);

function e($value)
{
  return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application for Adult Registration • BSP</title>
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

    /* Leader group inactive state */
    .leader-group-inactive {
      opacity: 0.4;
      filter: grayscale(0.5);
    }

    .leader-group-active {
      opacity: 1;
      border-color: #059669;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.1);
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
      <h1 class="text-2xl font-bold text-green-800 mb-1">Application for Adult Registration</h1>
      <p class="text-sm text-gray-600">Step 1: Personal and Scouting Information</p>
    </div>

    <!-- Progress Stepper -->
    <?php render_stepper(1); ?>

    <!-- Application Form -->
    <div class="card bg-white shadow-lg mt-6">
      <div class="card-body p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-800">Applicant Information</h2>
          <span class="badge badge-success text-sm px-3 py-2">Step 1</span>
        </div>

        <form id="step1-form" method="post" action="step1_process.php">
          <!-- Personal Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Surname</span>
              </label>
              <input id="surname" name="surname" type="text" placeholder="Enter surname" value="<?php echo e($app["surname"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">First Name</span>
              </label>
              <input id="firstname" name="firstname" type="text" placeholder="Enter first name" value="<?php echo e($app["firstname"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Middle Name</span>
              </label>
              <input id="mi" name="mi" type="text" placeholder="Enter middle name" value="<?php echo e($app["mi"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Sex</span>
              </label>
              <select id="sex" name="sex" class="select select-bordered w-full bg-green-100 text-gray-900" required>
                <option value="">Select</option>
                <option value="Male" <?php echo (isset($app["sex"]) && $app["sex"] === "Male") ? "selected" : ""; ?>>Male</option>
                <option value="Female" <?php echo (isset($app["sex"]) && $app["sex"] === "Female") ? "selected" : ""; ?>>Female</option>
                <option value="Prefer not to say" <?php echo (isset($app["sex"]) && $app["sex"] === "Prefer not to say") ? "selected" : ""; ?>>Prefer not to say</option>
              </select>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Civil Status</span>
              </label>
              <select id="civil-status" name="civil_status" class="select select-bordered w-full bg-green-100 text-gray-900" required>
                <option value="">Select</option>
                <option value="Single" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Single") ? "selected" : ""; ?>>Single</option>
                <option value="Married" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Married") ? "selected" : ""; ?>>Married</option>
                <option value="Widowed" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Widowed") ? "selected" : ""; ?>>Widowed</option>
                <option value="Separated" <?php echo (isset($app["civil_status"]) && $app["civil_status"] === "Separated") ? "selected" : ""; ?>>Separated</option>
              </select>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Tenure in Scouting</span>
              </label>
              <input id="tenure" name="tenure" type="text" placeholder="e.g., 5 years" value="<?php echo e($app["tenure"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
          </div>

          <!-- To serve as -->
          <div class="divider text-gray-700 font-semibold">To serve as (Choose only one)</div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Unit Leader -->
            <div class="border-2 border-gray-300 rounded-lg p-5 bg-green-100 transition-all duration-300" id="unit-leader-group">
              <div class="form-control mb-4">
                <label class="label cursor-pointer justify-start gap-3">
                  <input
                    type="radio"
                    name="serve_main"
                    value="unit"
                    id="serve_main_unit"
                    class="radio radio-success radio-lg"
                    <?php echo (isset($app["serve_main"]) && $app["serve_main"] === "unit") ? "checked" : ""; ?>
                    required />
                  <span class="font-bold text-lg text-gray-800">A. Unit Leader</span>
                </label>
              </div>
              <div class="ml-10 space-y-3">
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Langkay Leader/Assistant"
                    data-main="unit"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Langkay Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_1" />
                  <span class="text-gray-700 font-medium">Langkay Leader/Assistant</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Kawan Leader/Assistant"
                    data-main="unit"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Kawan Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_2" />
                  <span class="text-gray-700 font-medium">Kawan Leader/Assistant</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Troop Leader/Assistant"
                    data-main="unit"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Troop Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_3" />
                  <span class="text-gray-700 font-medium">Troop Leader/Assistant</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Outfit Leader/Assistant"
                    data-main="unit"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Outfit Leader/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_4" />
                  <span class="text-gray-700 font-medium">Outfit Leader/Assistant</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Circle Manager/Assistant"
                    data-main="unit"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Circle Manager/Assistant") ? "checked" : ""; ?>
                    id="sub_unit_5" />
                  <span class="text-gray-700 font-medium">Circle Manager/Assistant</span>
                </label>
              </div>
            </div>

            <!-- Lay Leader -->
            <div class="border-2 border-gray-300 rounded-lg p-5 bg-green-100 transition-all duration-300" id="lay-leader-group">
              <div class="form-control mb-4">
                <label class="label cursor-pointer justify-start gap-3">
                  <input
                    type="radio"
                    name="serve_main"
                    value="lay"
                    id="serve_main_lay"
                    class="radio radio-success radio-lg"
                    <?php echo (isset($app["serve_main"]) && $app["serve_main"] === "lay") ? "checked" : ""; ?>
                    required />
                  <span class="font-bold text-lg text-gray-800">B. Lay Leader</span>
                </label>
              </div>
              <div class="ml-10 space-y-3">
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Institutional Scouting Representative/ISCOM/ISC"
                    data-main="lay"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Institutional Scouting Representative/ISCOM/ISC") ? "checked" : ""; ?>
                    id="sub_lay_1" />
                  <span class="text-gray-700 font-medium whitespace-normal">Institutional Scouting Representative/ISCOM/ISC</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="District/Municipal Commissioner/Coordinator/Member-at-Large"
                    data-main="lay"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "District/Municipal Commissioner/Coordinator/Member-at-Large") ? "checked" : ""; ?>
                    id="sub_lay_2" />
                  <span class="text-gray-700 font-medium whitespace-normal">District/Municipal Commissioner/Coordinator/Member-at-Large</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3 py-2 hover:bg-gray-50 rounded px-2 transition-colors">
                  <input
                    type="radio"
                    name="serve_sub"
                    value="Local Council"
                    data-main="lay"
                    class="radio radio-success radio-sm"
                    <?php echo (isset($app["serve_sub"]) && $app["serve_sub"] === "Local Council") ? "checked" : ""; ?>
                    id="sub_lay_3" />
                  <span class="text-gray-700 font-medium whitespace-normal">Local Council</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Sponsoring Institutions</span>
              </label>
              <input id="sponsoring" name="sponsoring" type="text" placeholder="Enter sponsoring institution" value="<?php echo e($app["sponsoring_institutions"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Council</span>
              </label>
              <input id="council" name="council" type="text" placeholder="Enter council" value="<?php echo e($app["council"] ?? "Navotas City Council"); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Date of Birth</span>
              </label>
              <input id="dob" name="dob" type="date" placeholder="YYYY-MM-DD" value="<?php echo e($app["dob"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Place of Birth</span>
              </label>
              <input id="pob" name="pob" type="text" placeholder="Enter place of birth" value="<?php echo e($app["pob"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Religion</span>
              </label>
              <input id="religion" name="religion" type="text" placeholder="Enter religion" value="<?php echo e($app["religion"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Profession/Occupation</span>
              </label>
              <input id="profession" name="profession" type="text" placeholder="Enter profession/occupation" value="<?php echo e($app["profession"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
            <div class="form-control md:col-span-2 lg:col-span-3">
              <label class="label">
                <span class="label-text font-semibold text-gray-700">Position/Title</span>
              </label>
              <input id="position" name="position" type="text" placeholder="Enter position/title" value="<?php echo e($app["position_title"] ?? ""); ?>" class="input input-bordered w-full bg-green-100 text-gray-900" required />
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex justify-between gap-4">
            <?php if ($isEditing) : ?>
              <a class="btn btn-error" href="status.php">Cancel Edit</a>
            <?php else: ?>
              <div></div>
            <?php endif; ?>
            <button class="btn bg-green-700 hover:bg-green-800 text-white" type="submit">
              Proceed to Safe From Harm
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../../public/assets/js/app.js"></script>
  <script>
    // Visual feedback for leader group selection
    document.addEventListener('DOMContentLoaded', function() {
      const unitGroup = document.getElementById('unit-leader-group');
      const layGroup = document.getElementById('lay-leader-group');
      const unitRadio = document.getElementById('serve_main_unit');
      const layRadio = document.getElementById('serve_main_lay');

      function updateGroupStates() {
        if (unitRadio.checked) {
          unitGroup.classList.add('leader-group-active');
          unitGroup.classList.remove('leader-group-inactive');
          layGroup.classList.add('leader-group-inactive');
          layGroup.classList.remove('leader-group-active');
        } else if (layRadio.checked) {
          layGroup.classList.add('leader-group-active');
          layGroup.classList.remove('leader-group-inactive');
          unitGroup.classList.add('leader-group-inactive');
          unitGroup.classList.remove('leader-group-active');
        } else {
          // No selection - both neutral
          unitGroup.classList.remove('leader-group-active', 'leader-group-inactive');
          layGroup.classList.remove('leader-group-active', 'leader-group-inactive');
        }
      }

      // Listen to changes
      unitRadio.addEventListener('change', updateGroupStates);
      layRadio.addEventListener('change', updateGroupStates);

      // Also update when clicking directly on the group (allows switching)
      unitGroup.addEventListener('click', function(e) {
        // Allow clicking anywhere in the group to activate it
        if (layRadio.checked && !e.target.closest('input[type="radio"]')) {
          unitRadio.checked = true;
          updateGroupStates();
          // Trigger change event for app.js logic
          unitRadio.dispatchEvent(new Event('change'));
        }
      });

      layGroup.addEventListener('click', function(e) {
        if (unitRadio.checked && !e.target.closest('input[type="radio"]')) {
          layRadio.checked = true;
          updateGroupStates();
          // Trigger change event for app.js logic
          layRadio.dispatchEvent(new Event('change'));
        }
      });

      // Initialize on page load
      updateGroupStates();
    });
  </script>
</body>

</html>