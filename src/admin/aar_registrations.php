<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION["admin_id"])) {
  header("Location: ../../public/admin_login.php");
  exit;
}

$status = $_GET['status'] ?? 'pending';
$paymentJoin = "LEFT JOIN (SELECT p1.* FROM payments p1 INNER JOIN (SELECT user_id, MAX(uploaded_at) AS max_uploaded_at FROM payments GROUP BY user_id) p2 ON p1.user_id = p2.user_id AND p1.uploaded_at = p2.max_uploaded_at) p ON p.user_id = a.user_id";
$registrations = null;
if (in_array($status, ['pending', 'approved', 'rejected'])) {
  $baseSelect = "SELECT a.*, u.email, u.username, p.id AS payment_id, p.status AS payment_status FROM applications a JOIN users u ON a.user_id = u.id $paymentJoin";
  if ($status === 'approved') {
    $where = "WHERE a.status = 'approved' AND p.status = 'approved'";
  } elseif ($status === 'rejected') {
    $where = "WHERE a.status = 'rejected' AND (p.status IS NULL OR p.status != 'pending') OR (p.status = 'rejected' AND a.status = 'approved')";
  } else {
    $where = "WHERE (a.status = 'pending' OR (a.status = 'approved' AND (p.status = 'pending' OR p.status IS NULL)) OR (a.status = 'pending' AND p.status = 'rejected'))";
  }
  $registrations = $conn->query("$baseSelect $where ORDER BY a.created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AAR Registrations • BSP</title>
  <link rel="stylesheet" href="../../public/assets/css/output.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css" />
  <style>
    body { font-family: 'Montserrat', sans-serif; }

    /* DataTables v2 - make controls feel like Tailwind/DaisyUI */
    .dt-container {
      --dt-row-selected: rgba(31, 125, 83, 0.12);
    }

    .dt-layout-row {
      display: flex;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      margin: 12px 0;
    }

    .dt-layout-row .dt-search,
    .dt-layout-row .dt-length {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .dt-layout-row .dt-search input,
    .dt-layout-row .dt-length select {
      border: 1px solid rgba(0,0,0,0.14);
      border-radius: 12px;
      padding: 10px 12px;
      background: #fff;
      outline: none;
      min-height: 40px;
    }

    .dt-layout-row .dt-search input:focus,
    .dt-layout-row .dt-length select:focus {
      box-shadow: 0 0 0 3px rgba(31, 125, 83, 0.18);
      border-color: rgba(31, 125, 83, 0.6);
    }

    table.dataTable {
      border-collapse: separate !important;
      border-spacing: 0;
    }

    table.dataTable thead th {
      background: rgba(0,0,0,0.02);
      font-weight: 700;
      color: rgba(0,0,0,0.65);
    }

    table.dataTable tbody tr:hover {
      background: rgba(0,0,0,0.02);
    }

    .dt-paging button {
      border-radius: 10px !important;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-100">
  <div id="adminLayout" class="flex min-h-screen transition-all duration-300">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    <div class="flex flex-col w-full">
      <div class="flex items-start mt-4">
        <button id="sidebarToggle" onclick="toggleSidebar()" style="margin-left: 14px;" class="z-50 bg-[#1F7D53] text-white p-3 rounded-full shadow-lg hover:bg-[#186943] transition-all duration-300 focus:outline-none">
          <i class="fas fa-bars text-lg"></i>
        </button>
      </div>

      <main id="adminMain" class="main flex-1 bg-gray-100 p-4 md:p-6 transition-all duration-300">
        <div class="mb-5">
          <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
              <h1 class="text-2xl md:text-3xl font-bold text-gray-900">AAR Registrations</h1>
              <p class="text-sm text-gray-500 mt-1">Review applications, verify payment, and take action.</p>
            </div>
            <div class="tabs tabs-boxed bg-white shadow-sm">
              <a href="?status=pending" class="tab <?= $status === 'pending' ? 'tab-active' : '' ?>">Pending</a>
              <a href="?status=approved" class="tab <?= $status === 'approved' ? 'tab-active' : '' ?>">Approved</a>
              <a href="?status=rejected" class="tab <?= $status === 'rejected' ? 'tab-active' : '' ?>">Rejected</a>
            </div>
          </div>
        </div>

        <div class="card bg-white shadow-xl">
          <div class="card-body p-4 md:p-6">
            <div class="flex items-center justify-between gap-3 flex-wrap">
              <h2 class="text-lg md:text-xl font-semibold text-gray-900"><?= ucfirst($status) ?> Applications</h2>
              <div class="text-xs text-gray-500">Tip: use search to quickly find by name or email.</div>
            </div>
            <div class="mt-4 overflow-x-auto">
              <table id="myTable" class="table w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Council</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php while ($registrations && $row = $registrations->fetch_assoc()): ?>
                <tr>
                  <td class="px-4 py-3">
                    <strong class="text-green-900 text-base"><?= htmlspecialchars($row['surname'] . ' ' . $row['firstname']) ?></strong><br>
                    <span class="text-gray-500 text-sm"><?= htmlspecialchars($row['email']) ?></span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['council']) ?></td>
                  <td class="px-4 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['serve_sub']) ?></td>
                  <td class="px-4 py-3">
                    <?php
                    $paymentStatus = $row['payment_status'] ?? 'not-submitted';
                    $paymentLabel = $paymentStatus === 'not-submitted' ? 'Not Submitted' : ucfirst($paymentStatus);
                    $paymentClass = $paymentStatus === 'not-submitted' ? 'badge badge-neutral' : ($paymentStatus === 'approved' ? 'badge badge-success' : ($paymentStatus === 'rejected' ? 'badge badge-error' : 'badge badge-warning'));
                    ?>
                    <div class="flex flex-col gap-1">
                      <span class="badge <?= $row['status'] === 'approved' ? 'badge-success' : ($row['status'] === 'rejected' ? 'badge-error' : 'badge-warning') ?>">AAR Form <?= ucfirst($row['status']) ?></span>
                      <span class="<?= $paymentClass ?>">Payment <?= $paymentLabel ?></span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                  <td class="px-4 py-3">
                    <button class="btn btn-primary btn-sm View" data-user-id="<?= (int)$row['user_id'] ?>">View</button>
                    <?php if ($row['status'] === 'pending'): ?>
                      <form method="post" action="admin_action.php" class="inline">
                        <input type="hidden" name="action" value="approve_app" />
                        <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                        <button class="btn btn-success btn-sm" type="submit">Approve Form</button>
                      </form>
                      <form method="post" action="admin_action.php" class="inline">
                        <input type="hidden" name="action" value="reject_app" />
                        <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                        <button class="btn btn-error btn-sm" type="submit">Reject Form</button>
                      </form>
                    <?php endif; ?>
                    <?php if ($paymentStatus === 'pending' && !empty($row['payment_id'])): ?>
                      <form method="post" action="admin_action.php" class="inline">
                        <input type="hidden" name="action" value="approve_payment" />
                        <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>" />
                        <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                        <button class="btn btn-success btn-sm" type="submit">Approve Payment</button>
                      </form>
                      <form method="post" action="admin_action.php" class="inline">
                        <input type="hidden" name="action" value="reject_payment" />
                        <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>" />
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                        <button class="btn btn-error btn-sm" type="submit">Reject Payment</button>
                      </form>
                    <?php endif; ?>
                    <form method="post" action="admin_action.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this application?');">
                      <input type="hidden" name="action" value="delete_app" />
                      <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                      <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                      <button class="btn btn-neutral btn-sm" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
            </div>
          </div>
        </div>

        <!-- Modal for viewing registration details -->
        <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 items-center justify-center hidden">
          <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-auto relative p-8">
            <button id="closeModal" class="absolute top-4 right-6 text-2xl text-gray-500 hover:text-gray-700 bg-transparent border-none cursor-pointer">&times;</button>
            <div id="modalContent"></div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (document.getElementById('myTable')) {
        let table = new DataTable('#myTable', {
          pageLength: 10,
          lengthMenu: [10, 25, 50, 100],
          autoWidth: false,
        });
      }
    });
  </script>

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
            const viewModal = document.getElementById('viewModal');
            viewModal.classList.remove('hidden');
            viewModal.classList.add('flex');

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
      const viewModal = document.getElementById('viewModal');
      viewModal.classList.add('hidden');
      viewModal.classList.remove('flex');
    };
    window.onclick = function(event) {
      if (event.target === document.getElementById('viewModal')) {
        const viewModal = document.getElementById('viewModal');
        viewModal.classList.add('hidden');
        viewModal.classList.remove('flex');
      }
    };
  </script>
</body>
</html>
