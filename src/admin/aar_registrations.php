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
  <title>AAR Registrations &bull; BSP</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../public/assets/css/output.css" />
  <style>
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ── Table typography ── */
    #regTable th {
      font-size: 0.7rem;
      letter-spacing: 0.06em;
    }

    #regTable td {
      font-size: 0.8rem;
    }

    /* ── Zebra striping ── */
    #regTable tbody tr:nth-child(even):not(.hidden) {
      background-color: #f9fafb;
    }

    #regTable tbody tr:hover {
      background-color: #f0fdf4 !important;
    }

    /* ── Action dropdown ── */
    .action-menu {
      display: none;
      position: absolute;
      right: 0;
      top: 100%;
      z-index: 30;
      min-width: 160px;
    }

    .action-menu.open {
      display: block;
    }

    /* ── Select overrides ── */
    select,
    select option {
      color: #1e293b !important;
    }

    /* ── Modal ── */
    #viewModal>div {
      max-width: 820px;
      width: 96%;
    }

    @media (max-width: 768px) {
      #viewModal>div {
        max-width: 100%;
        height: 100%;
        border-radius: 0;
        padding: 1rem;
      }
    }

    /* ── Scrollbar (webkit) ── */
    .table-wrap::-webkit-scrollbar {
      height: 6px;
    }

    .table-wrap::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 3px;
    }

    /* ── Mobile cards ── */
    .m-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      padding: 0.85rem 1rem;
      margin-bottom: 0.5rem;
    }
  </style>
</head>

<body class="min-h-screen bg-gray-50 overflow-x-hidden">
  <div id="adminLayout" class="flex min-h-screen w-full transition-all duration-300">
    <?php include __DIR__ . '/admin_sidebar.php'; ?>

    <div class="flex flex-col w-full min-w-0">
      <!-- Sidebar toggle -->
      <div class="flex items-start mt-4">
        <button id="sidebarToggle" onclick="toggleSidebar()" style="margin-left:14px" class="z-50 bg-[#1F7D53] text-white p-3 rounded-full shadow-lg hover:bg-[#166540] transition-all duration-300 focus:outline-none">
          <i class="fas fa-bars text-lg"></i>
        </button>
      </div>

      <main id="adminMain" class="main flex-1 min-w-0 p-4 md:p-8 transition-all duration-300">

        <!-- ════════ Page Title ════════ -->
        <div class="mb-6">
          <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">AAR Registrations</h1>
          <p class="text-gray-500 text-sm mt-0.5">Review and manage all applications efficiently</p>
        </div>

        <!-- ════════ Card ════════ -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

          <!-- ── Toolbar ── -->
          <div class="border-b border-gray-200 px-4 py-3 md:px-6 md:py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
              <!-- Left: Search + Filters -->
              <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search -->
                <div class="relative">
                  <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                  <input id="tableSearch" type="text"
                    class="w-56 md:w-72 h-9 pl-9 pr-3 text-sm rounded-lg border border-gray-300 bg-white placeholder-gray-400 focus:border-[#1F7D53] focus:ring-2 focus:ring-[#1F7D53]/20 outline-none transition"
                    placeholder="Search applicant, email..." />
                </div>

                <!-- Divider -->
                <div class="hidden lg:block w-px h-7 bg-gray-200"></div>

                <!-- Status filter -->
                <div class="flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-300 bg-white">
                  <i class="fas fa-filter text-gray-400 text-[11px]"></i>
                  <select id="statusFilter" class="bg-transparent border-0 text-sm font-medium outline-none cursor-pointer pr-1 h-full">
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                  </select>
                </div>

                <!-- Show per page -->
                <div class="flex items-center gap-1.5 h-9 px-3 rounded-lg border border-gray-300 bg-white">
                  <span class="text-xs text-gray-500 font-medium">Show</span>
                  <select id="rowsPerPage" class="bg-transparent border-0 text-sm font-semibold outline-none cursor-pointer pr-1">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                  </select>
                </div>

                <!-- Entry count badge -->
                <span class="hidden md:inline-flex items-center gap-1 text-xs text-gray-500 font-medium ml-1">
                  <span id="totalCount" class="font-bold text-[#1F7D53]">0</span> entries
                </span>
              </div>

              <!-- Right: Pagination -->
              <!-- Pagination controls removed (footer only) -->
            </div>
          </div>

          <!-- ── Table (Desktop) ── -->
          <div class="table-wrap overflow-x-auto min-w-0">
            <table id="regTable" class="hidden md:table w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Applicant</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Council</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Role</th>
                  <th class="sticky top-0 z-10 text-center px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Status</th>
                  <th class="sticky top-0 z-10 text-left px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap">Submitted</th>
                  <th class="sticky top-0 z-10 text-center px-4 py-3 font-semibold text-gray-500 uppercase bg-gray-50 whitespace-nowrap w-24">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php $i = 0;
                $mobileCards = '';
                while ($registrations && $row = $registrations->fetch_assoc()): $idx = $i++;
                  $paymentStatus = $row['payment_status'] ?? 'not-submitted';
                  $paymentLabel  = $paymentStatus === 'not-submitted' ? 'No Payment' : ucfirst($paymentStatus);
                  $appBadge      = $row['status'] === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($row['status'] === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700');
                  $payBadge      = $paymentStatus === 'not-submitted' ? 'bg-gray-100 text-gray-600' : ($paymentStatus === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($paymentStatus === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'));
                ?>
                  <tr data-index="<?= $idx ?>" class="table-row-item transition-colors duration-100">
                    <!-- Applicant -->
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#1F7D53] flex items-center justify-center text-white text-xs font-bold shrink-0">
                          <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['surname'], 0, 1)) ?>
                        </div>
                        <div class="min-w-0">
                          <div class="font-semibold text-gray-900 text-sm truncate"><?= htmlspecialchars($row['surname'] . ', ' . $row['firstname']) ?></div>
                          <div class="text-gray-400 text-xs truncate"><?= htmlspecialchars($row['email']) ?></div>
                        </div>
                      </div>
                    </td>
                    <!-- Council -->
                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap"><?= htmlspecialchars($row['council']) ?></td>
                    <!-- Role -->
                    <td class="px-4 py-3 text-gray-600 max-w-[200px]">
                      <span class="block truncate" title="<?= htmlspecialchars($row['serve_sub']) ?>"><?= htmlspecialchars($row['serve_sub']) ?></span>
                    </td>
                    <!-- Status -->
                    <td class="px-4 py-3 text-center">
                      <div class="inline-flex flex-col gap-1 items-center">
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold <?= $appBadge ?>"><?= ucfirst($row['status']) ?></span>
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium <?= $payBadge ?>"><?= $paymentLabel ?></span>
                      </div>
                    </td>
                    <!-- Submitted -->
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-sm"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                    <!-- Actions (inline buttons) -->
                    <td class="px-4 py-3 text-center">
                      <div class="flex items-center justify-center gap-1">
                        <button type="button" class="View btn btn-ghost btn-sm tooltip tooltip-top" data-user-id="<?= (int)$row['user_id'] ?>" title="View">
                          <i class="fas fa-eye text-blue-600"></i>
                        </button>

                        <?php if ($row['status'] === 'pending'): ?>
                          <form method="post" action="admin_action.php" class="inline">
                            <input type="hidden" name="action" value="approve_app" />
                            <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                            <button type="submit" class="btn btn-ghost btn-sm tooltip tooltip-top" title="Approve">
                              <i class="fas fa-check text-green-600"></i>
                            </button>
                          </form>
                          <form method="post" action="admin_action.php" class="inline">
                            <input type="hidden" name="action" value="reject_app" />
                            <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                            <button type="submit" class="btn btn-ghost btn-sm tooltip tooltip-top" title="Reject">
                              <i class="fas fa-times text-red-600"></i>
                            </button>
                          </form>
                        <?php endif; ?>

                        <?php if ($paymentStatus === 'pending' && !empty($row['payment_id'])): ?>
                          <form method="post" action="admin_action.php" class="inline">
                            <input type="hidden" name="action" value="approve_payment" />
                            <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>" />
                            <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                            <button type="submit" class="btn btn-ghost btn-sm tooltip tooltip-top" title="Approve Payment">
                              <i class="fas fa-dollar-sign text-emerald-600"></i>
                            </button>
                          </form>
                          <form method="post" action="admin_action.php" class="inline">
                            <input type="hidden" name="action" value="reject_payment" />
                            <input type="hidden" name="payment_id" value="<?= (int)$row['payment_id'] ?>" />
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                            <button type="submit" class="btn btn-ghost btn-sm tooltip tooltip-top" title="Reject Payment">
                              <i class="fas fa-ban text-rose-600"></i>
                            </button>
                          </form>
                        <?php endif; ?>

                        <form method="post" action="admin_action.php" onsubmit="return confirm('Delete this application?');" class="inline ml-1">
                          <input type="hidden" name="action" value="delete_app" />
                          <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                          <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                          <button type="submit" class="btn btn-ghost btn-sm tooltip tooltip-top" title="Delete">
                            <i class="fas fa-trash-alt text-red-600"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>

                  <?php
                  // ── Buffer mobile card ──
                  ob_start();
                  ?>
                  <div data-index="<?= $idx ?>" class="m-card block md:hidden">
                    <div class="flex items-start gap-3">
                      <div class="w-9 h-9 rounded-full bg-[#1F7D53] flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5">
                        <?= strtoupper(substr($row['firstname'], 0, 1) . substr($row['surname'], 0, 1)) ?>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                          <div class="min-w-0">
                            <div class="font-semibold text-gray-900 text-sm truncate"><?= htmlspecialchars($row['surname'] . ', ' . $row['firstname']) ?></div>
                            <div class="text-gray-400 text-xs truncate"><?= htmlspecialchars($row['email']) ?></div>
                          </div>
                          <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold <?= $appBadge ?>"><?= ucfirst($row['status']) ?></span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium <?= $payBadge ?>"><?= $paymentLabel ?></span>
                          </div>
                        </div>
                        <div class="mt-1.5 text-xs text-gray-500">
                          <span><?= htmlspecialchars($row['council']) ?></span>
                          <span class="mx-1">&middot;</span>
                          <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate"><?= htmlspecialchars($row['serve_sub']) ?></div>
                        <!-- Mobile actions -->
                        <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100">
                          <button class="View btn btn-ghost btn-xs" data-user-id="<?= (int)$row['user_id'] ?>" title="View">
                            <i class="fas fa-eye text-blue-600"></i>
                          </button>
                          <?php if ($row['status'] === 'pending'): ?>
                            <form method="post" action="admin_action.php" class="inline">
                              <input type="hidden" name="action" value="approve_app" />
                              <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                              <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                              <button type="submit" class="btn btn-ghost btn-xs" title="Approve">
                                <i class="fas fa-check text-green-600"></i>
                              </button>
                            </form>
                            <form method="post" action="admin_action.php" class="inline">
                              <input type="hidden" name="action" value="reject_app" />
                              <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                              <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                              <button type="submit" class="btn btn-ghost btn-xs" title="Reject">
                                <i class="fas fa-times text-red-600"></i>
                              </button>
                            </form>
                          <?php endif; ?>
                          <form method="post" action="admin_action.php" class="inline ml-auto" onsubmit="return confirm('Delete?');">
                            <input type="hidden" name="action" value="delete_app" />
                            <input type="hidden" name="user_id" value="<?= (int)$row['user_id'] ?>" />
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($status) ?>" />
                            <button type="submit" class="btn btn-ghost btn-xs" title="Delete">
                              <i class="fas fa-trash-alt text-red-500"></i>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php $mobileCards .= ob_get_clean(); ?>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <!-- ── Mobile cards ── -->
          <?php if (!empty($mobileCards)): ?>
            <div class="md:hidden px-4 py-3"><?= $mobileCards ?></div>
          <?php endif; ?>

          <!-- ── Footer ── -->
          <div class="border-t border-gray-200 px-4 py-3 md:px-6 flex items-center justify-between text-xs text-gray-500">
            <span id="tableInfoFooter">Showing 0 to 0 of 0 entries</span>
            <div class="flex items-center gap-1.5">
              <button id="prevPageFooter" class="h-8 px-2.5 rounded border border-gray-300 bg-white text-gray-600 hover:bg-[#1F7D53] hover:text-white hover:border-[#1F7D53] disabled:opacity-40 disabled:hover:bg-white disabled:hover:text-gray-600 disabled:hover:border-gray-300 transition text-xs">
                <i class="fas fa-chevron-left text-[9px]"></i> Prev
              </button>
              <button id="nextPageFooter" class="h-8 px-2.5 rounded border border-gray-300 bg-white text-gray-600 hover:bg-[#1F7D53] hover:text-white hover:border-[#1F7D53] disabled:opacity-40 disabled:hover:bg-white disabled:hover:text-gray-600 disabled:hover:border-gray-300 transition text-xs">
                Next <i class="fas fa-chevron-right text-[9px]"></i>
              </button>
            </div>
          </div>

        </div><!-- /card -->

        <!-- ════════ Modal ════════ -->
        <div id="viewModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 items-center justify-center hidden">
          <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-auto relative p-6 md:p-8">
            <button id="closeModal" class="absolute top-4 right-5 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition">&times;</button>
            <div id="modalContent"></div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- ════════ Scripts ════════ -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      /* ── Refs ── */
      const searchInput = document.getElementById('tableSearch');
      const rowsPerPageSelect = document.getElementById('rowsPerPage');
      const prevBtn = document.getElementById('prevPage');
      const nextBtn = document.getElementById('nextPage');
      const prevBtnF = document.getElementById('prevPageFooter');
      const nextBtnF = document.getElementById('nextPageFooter');
      const infoTop = document.getElementById('tableInfo');
      const infoBot = document.getElementById('tableInfoFooter');
      const totalCountEl = document.getElementById('totalCount');

      /* ── Build item list ── */
      const indexedEls = Array.from(document.querySelectorAll('[data-index]')).map(el => el.getAttribute('data-index'));
      const uniqueIndices = [...new Set(indexedEls)].sort((a, b) => +a - +b);
      const items = uniqueIndices.map(idx => {
        const tableEl = document.querySelector('tr[data-index="' + idx + '"]');
        const cardEl = document.querySelector('.m-card[data-index="' + idx + '"]');
        const text = ((tableEl && tableEl.textContent) || '') + ' ' + ((cardEl && cardEl.textContent) || '');
        return {
          idx,
          tableEl,
          cardEl,
          text
        };
      });

      let filtered = items.slice();
      let currentPage = 1;
      let rowsPerPage = parseInt(rowsPerPageSelect.value, 10);
      totalCountEl.textContent = items.length;

      function render() {
        const total = filtered.length;
        const totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        items.forEach(it => {
          if (it.tableEl) it.tableEl.classList.add('hidden');
          if (it.cardEl) it.cardEl.classList.add('hidden');
        });
        filtered.slice(start, end).forEach(it => {
          if (it.tableEl) it.tableEl.classList.remove('hidden');
          if (it.cardEl) it.cardEl.classList.remove('hidden');
        });

        const from = total === 0 ? 0 : start + 1;
        const to = Math.min(end, total);
        const msg = `Showing ${from} to ${to} of ${total}`;
        infoTop.textContent = msg;
        infoBot.textContent = msg;

        [prevBtn, prevBtnF].forEach(b => b.disabled = currentPage === 1);
        [nextBtn, nextBtnF].forEach(b => b.disabled = currentPage >= totalPages || total === 0);
      }

      function applySearch() {
        const term = (searchInput.value || '').trim().toLowerCase();
        filtered = !term ? items.slice() : items.filter(it => it.text.toLowerCase().includes(term));
        currentPage = 1;
        render();
      }

      searchInput.addEventListener('input', applySearch);
      rowsPerPageSelect.addEventListener('change', function() {
        rowsPerPage = +this.value;
        currentPage = 1;
        render();
      });
      [prevBtn, prevBtnF].forEach(b => b.addEventListener('click', () => {
        if (currentPage > 1) {
          currentPage--;
          render();
        }
      }));
      [nextBtn, nextBtnF].forEach(b => b.addEventListener('click', () => {
        const tp = Math.max(1, Math.ceil(filtered.length / rowsPerPage));
        if (currentPage < tp) {
          currentPage++;
          render();
        }
      }));
      render();

      /* ── Status filter navigation ── */
      document.getElementById('statusFilter').addEventListener('change', function() {
        const u = new URL(window.location.href);
        u.searchParams.set('status', this.value || 'pending');
        window.location.href = u.pathname + '?' + u.searchParams.toString();
      });

      /* ── Action dropdown toggle ── */
      document.addEventListener('click', function(e) {
        const toggle = e.target.closest('.action-toggle');
        // close all open menus
        document.querySelectorAll('.action-menu.open').forEach(m => {
          if (!toggle || m !== toggle.nextElementSibling) m.classList.remove('open');
        });
        if (toggle) {
          e.stopPropagation();
          toggle.nextElementSibling.classList.toggle('open');
        }
      });
    });
  </script>

  <!-- View modal logic -->
  <script>
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.View');
      if (!btn) return;
      const userId = btn.getAttribute('data-user-id');
      // close any open action menu
      document.querySelectorAll('.action-menu.open').forEach(m => m.classList.remove('open'));

      fetch('view_registration.php?user_id=' + userId)
        .then(r => r.json())
        .then(data => {
          let html = `<div class='grid grid-cols-1 md:grid-cols-2 gap-6'>`;
          html += `<div><h2 class='text-lg font-bold text-gray-900 mb-4'>AAR Form</h2><table class='w-full text-sm'>`;
          for (const [key, value] of Object.entries(data.summary)) {
            html += `<tr class='border-b border-gray-100'><td class='font-medium py-2 pr-4 text-gray-500'>${key}</td><td class='py-2 text-gray-900'>${value}</td></tr>`;
          }
          html += `</table></div>`;
          html += `<div><h2 class='text-lg font-bold text-gray-900 mb-4'>Payment Images</h2>`;
          if (data.payment_front || data.payment_back) {
            html += `<div class='flex flex-wrap gap-4'>`;
            if (data.payment_front) html += `<img src='../../${data.payment_front}' alt='Front' class='payment-img rounded-lg border border-gray-200 cursor-pointer max-w-[200px] max-h-[160px] hover:shadow-md transition'>`;
            if (data.payment_back) html += `<img src='../../${data.payment_back}'  alt='Back'  class='payment-img rounded-lg border border-gray-200 cursor-pointer max-w-[200px] max-h-[160px] hover:shadow-md transition'>`;
            html += `</div>`;
          } else {
            html += `<p class='text-gray-400 text-sm'>No payment images uploaded.</p>`;
          }
          html += `</div></div>`;

          document.getElementById('modalContent').innerHTML = html;
          const vm = document.getElementById('viewModal');
          vm.classList.remove('hidden');
          vm.classList.add('flex');

          /* Enlarge image sub-modal */
          if (!document.getElementById('paymentImageModal')) {
            const m = document.createElement('div');
            m.id = 'paymentImageModal';
            m.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center hidden';
            m.innerHTML = `<div class='relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col items-center p-6'>
            <button id='closePaymentImgModal' class='absolute top-3 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500'>&times;</button>
            <img id='paymentImgModalImg' src='' class='rounded border border-gray-200 max-w-[80vw] max-h-[80vh]'>
          </div>`;
            document.body.appendChild(m);
            document.getElementById('closePaymentImgModal').onclick = () => m.classList.add('hidden');
            m.onclick = e => {
              if (e.target === m) m.classList.add('hidden');
            };
          }
          document.querySelectorAll('.payment-img').forEach(img => {
            img.onclick = () => {
              document.getElementById('paymentImgModalImg').src = img.src;
              document.getElementById('paymentImageModal').classList.remove('hidden');
            };
          });
        });
    });
    document.getElementById('closeModal').onclick = () => {
      const vm = document.getElementById('viewModal');
      vm.classList.add('hidden');
      vm.classList.remove('flex');
    };
    window.onclick = e => {
      if (e.target === document.getElementById('viewModal')) {
        const vm = document.getElementById('viewModal');
        vm.classList.add('hidden');
        vm.classList.remove('flex');
      }
    };
  </script>
</body>

</html>