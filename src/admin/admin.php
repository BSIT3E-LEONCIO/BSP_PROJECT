<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

// Dashboard stats
$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = a.user_id";
$pendingCount = $conn->query("SELECT COUNT(*) AS cnt FROM applications a $paymentJoin WHERE a.status != 'rejected' AND (p.status IS NULL OR p.status != 'rejected') AND (a.status = 'pending' OR p.status = 'pending' OR p.status IS NULL)")->fetch_assoc()['cnt'];
$approvedCount = $conn->query("SELECT COUNT(*) AS cnt FROM applications a $paymentJoin WHERE a.status = 'approved' AND p.status = 'approved'")->fetch_assoc()['cnt'];
$rejectedCount = $conn->query("SELECT COUNT(*) AS cnt FROM applications a $paymentJoin WHERE a.status = 'rejected' OR p.status = 'rejected'")->fetch_assoc()['cnt'];
$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];

// Recent Decisions
$recent = $conn->query("SELECT a.*, u.email FROM applications a JOIN users u ON a.user_id = u.id $paymentJoin WHERE a.status = 'approved' AND p.status = 'approved' ORDER BY a.updated_at DESC LIMIT 1")->fetch_assoc();

$view = $_GET['view'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard • BSP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/output.css" />
  <!-- Font Awesome CDN for sidebar icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <style>
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
  </style>
</head>

<body class="min-h-screen bg-gray-100">
  <div id="adminLayout" class="flex min-h-screen transition-all duration-300">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <div class="flex flex-col w-full">
      <!-- Hamburger Toggle Button at the top beside sidebar -->
      <div class="flex items-start mt-4">
        <button id="sidebarToggle" onclick="toggleSidebar()" style="margin-left: 14px;" class="z-50 bg-[#1F7D53] text-white p-3 rounded-full shadow-lg hover:bg-[#186943] transition-all duration-300 focus:outline-none">
          <i class="fas fa-bars text-lg"></i>
        </button>
      </div>
      <main id="adminMain" class="main flex-1 bg-gray-100 p-4 md:p-6 transition-all duration-300">
        <?php if ($view === 'dashboard'): ?>
          <h2 class="text-xl md:text-2xl font-semibold mb-4 md:mb-6 text-black">Admin Dashboard</h2>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white rounded-lg shadow p-4 md:p-5 flex flex-col items-center">
              <h3 class="text-gray-500 mb-1 md:mb-2 text-base md:text-sm">Pending AAR</h3>
              <div class="text-xl md:text-2xl font-bold text-green-700"><?= $pendingCount ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 md:p-5 flex flex-col items-center">
              <h3 class="text-gray-500 mb-1 md:mb-2 text-base md:text-sm">Approved AAR</h3>
              <div class="text-xl md:text-2xl font-bold text-green-700"><?= $approvedCount ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 md:p-5 flex flex-col items-center">
              <h3 class="text-gray-500 mb-1 md:mb-2 text-base md:text-sm">Rejected AAR</h3>
              <div class="text-xl md:text-2xl font-bold text-red-700"><?= $rejectedCount ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 md:p-5 flex flex-col items-center">
              <h3 class="text-gray-500 mb-1 md:mb-2 text-base md:text-sm">Total Users</h3>
              <div class="text-xl md:text-2xl font-bold text-blue-700"><?= $totalUsers ?></div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6 md:mb-8">
            <h3 class="text-base md:text-lg font-semibold mb-2 md:mb-4 text-black">Recent Decisions</h3>
            <?php if ($recent): ?>
              <div class="flex items-center gap-4">
                <div>
                  <strong class="text-lg text-green-900"><?= htmlspecialchars($recent['surname'] . ' ' . $recent['firstname']) ?></strong><br>
                  <span class="text-gray-500 text-sm"><?= htmlspecialchars($recent['email']) ?></span>
                </div>
                <span class="badge badge-success">Approved</span>
                <span class="text-gray-400 text-sm"><?= date('M d, Y h:i A', strtotime($recent['updated_at'])) ?></span>
              </div>
            <?php else: ?>
              <p class="text-gray-500">No recent approvals.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <!-- Modal for viewing registration details -->
        <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
          <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-auto relative p-8">
            <button id="closeModal" class="absolute top-4 right-6 text-2xl text-gray-500 hover:text-gray-700 bg-transparent border-none cursor-pointer">&times;</button>
            <div id="modalContent">
              <!-- Details will be loaded here -->
            </div>
          </div>
        </div>
        <script>
          document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn') && e.target.classList.contains('View')) {
              const userId = e.target.getAttribute('data-user-id');
              fetch('view_registration.php?user_id=' + userId)
                .then(res => res.json())
                .then(data => {
                  let html = `<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>`;
                  html += `<div><h2 class='text-xl font-bold mb-4'>AAR Form</h2><table class='w-full mb-4'>`;
                  for (const [key, value] of Object.entries(data.summary)) {
                    html += `<tr><td class='font-semibold py-2 pr-4 text-gray-700'>${key}</td><td class='py-2 text-gray-900'>${value}</td></tr>`;
                  }
                  html += `</table></div>`;

                  html += `<div><h2 class='text-xl font-bold mb-4'>Payment Images</h2>`;
                  if (data.payment_front || data.payment_back) {
                    html += `<div class='flex flex-wrap gap-4'>`;
                    if (data.payment_front) {
                      html += `<img src='../../${data.payment_front}' alt='Payment Front' class='payment-img rounded-lg border border-gray-300 cursor-pointer max-w-[220px] max-h-[180px]'>`;
                    }
                    if (data.payment_back) {
                      html += `<img src='../../${data.payment_back}' alt='Payment Back' class='payment-img rounded-lg border border-gray-300 cursor-pointer max-w-[220px] max-h-[180px]'>`;
                    }
                    html += `</div>`;
                  } else {
                    html += `<p class='text-gray-500'>No payment images uploaded.</p>`;
                  }
                  html += `</div></div>`;

                  document.getElementById('modalContent').innerHTML = html;
                  document.getElementById('viewModal').style.display = 'flex';

                  // Payment image modal
                  if (!document.getElementById('paymentImageModal')) {
                    const imgModal = document.createElement('div');
                    imgModal.id = 'paymentImageModal';
                    imgModal.className = 'fixed inset-0 bg-black bg-opacity-60 z-60 flex items-center justify-center hidden';
                    imgModal.innerHTML = `<div class='relative bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col items-center p-8'>
                <button id='closePaymentImgModal' class='absolute top-4 right-6 text-2xl text-gray-500 hover:text-gray-700 bg-transparent border-none cursor-pointer'>&times;</button>
                <img id='paymentImgModalImg' src='' alt='Payment Image' class='rounded-lg border border-gray-300 max-w-[80vw] max-h-[80vh]'>
              </div>`;
                    document.body.appendChild(imgModal);
                    document.getElementById('closePaymentImgModal').onclick = function() {
                      imgModal.classList.add('hidden');
                    };
                    imgModal.onclick = function(event) {
                      if (event.target === imgModal) {
                        imgModal.classList.add('hidden');
                      }
                    };
                  }
                  Array.from(document.querySelectorAll('.payment-img')).forEach(img => {
                    img.onclick = function() {
                      const modal = document.getElementById('paymentImageModal');
                      const modalImg = document.getElementById('paymentImgModalImg');
                      modalImg.src = img.src;
                      modal.classList.remove('hidden');
                    };
                  });
                });
            }
          });
          document.getElementById('closeModal').onclick = function() {
            document.getElementById('viewModal').classList.add('hidden');
          };
          window.onclick = function(event) {
            if (event.target === document.getElementById('viewModal')) {
              document.getElementById('viewModal').classList.add('hidden');
            }
          };
        </script>
      </main>
    </div>
</body>

</html>