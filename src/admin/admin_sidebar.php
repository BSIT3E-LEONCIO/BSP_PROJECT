<?php
// admin_sidebar.php
// This file renders the admin sidebar for all admin pages.
$sidebarView = $_GET['view'] ?? ($_GET['status'] ?? 'dashboard');
?>
<aside id="adminSidebar" class="min-h-screen flex flex-col text-white transition-all duration-300 shadow-2xl w-64 min-w-16 relative" style="background: linear-gradient(to bottom, #1F7D53, #186943);">
    <!-- Logo Section -->
    <div class="flex flex-col items-center py-6 px-4 border-b" style="border-color: rgba(255,255,255,0.1);">
        <div class="p-2 rounded-xl mb-3" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px);">
            <img id="sidebarLogo" src="../../public/assets/images/NCC-BSP.png" alt="BSP Logo" class="sidebar-logo w-16 transition-all duration-300" />
        </div>
        <div class="sidebar-text text-center">
            <h3 class="text-sm font-bold tracking-wide">BSP Portal</h3>
            <p class="text-xs mt-0.5" style="color: rgba(255,255,255,0.7);">Admin Panel</p>
        </div>
    </div>

    <!-- Navigation Section -->
    <nav id="sidebarNav" class="flex-1 px-3 py-4 overflow-y-auto">
        <!-- Dashboard Link -->
        <div class="mb-6">
            <a href="admin.php?view=dashboard" title="Dashboard" class="sidebar-link group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium transition-all duration-200 hover:translate-x-1 <?= $sidebarView === 'dashboard' ? 'shadow-lg' : '' ?>" style="<?= $sidebarView === 'dashboard' ? 'background: rgba(255,255,255,0.2);' : '' ?>" onmouseover="if('<?= $sidebarView ?>' !== 'dashboard') this.style.background='rgba(255,255,255,0.15)'" onmouseout="if('<?= $sidebarView ?>' !== 'dashboard') this.style.background=''">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg transition-all" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    <i class="fas fa-tachometer-alt text-lg"></i>
                </div>
                <span class="sidebar-text">Dashboard</span>
            </a>
        </div>

        <!-- AAR Registrations Collapsible Section -->
        <div class="mb-6">
            <div class="mb-2 px-3">
                <p class="sidebar-text text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.6);">Registrations</p>
            </div>
            <button type="button" onclick="toggleAARMenu()" title="AAR Applications" class="w-full group flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background=''">
                <div class="flex items-center gap-3 w-full">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg transition-all" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                        <i class="fas fa-clipboard-list text-lg"></i>
                    </div>
                    <span class="sidebar-text">AAR Applications</span>
                </div>
                <svg id="aarCaret" class="w-4 h-4 transition-transform duration-200 <?= in_array($sidebarView, ['pending', 'approved', 'rejected']) ? 'rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="aarSubmenu" class="<?= in_array($sidebarView, ['pending', 'approved', 'rejected']) ? '' : 'hidden' ?> mt-2 ml-4 space-y-1 border-l-2 pl-2" style="border-color: rgba(255,255,255,0.1);">
                <a href="aar_registrations.php?status=pending" title="Pending" class="sidebar-link group flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 hover:translate-x-1 <?= $sidebarView === 'pending' ? '' : '' ?>" style="<?= $sidebarView === 'pending' ? 'background: rgba(255,255,255,0.15);' : '' ?>" onmouseover="if('<?= $sidebarView ?>' !== 'pending') this.style.background='rgba(255,255,255,0.1)'" onmouseout="if('<?= $sidebarView ?>' !== 'pending') this.style.background=''">
                    <i class="fas fa-hourglass-half text-base opacity-70 group-hover:opacity-100"></i>
                    <span class="sidebar-text">Pending</span>
                    <?php if ($sidebarView === 'pending'): ?>
                        <span class="ml-auto w-1.5 h-1.5 rounded-full" style="background: #facc15;"></span>
                    <?php endif; ?>
                </a>
                <a href="aar_registrations.php?status=approved" title="Approved" class="sidebar-link group flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 hover:translate-x-1 <?= $sidebarView === 'approved' ? '' : '' ?>" style="<?= $sidebarView === 'approved' ? 'background: rgba(255,255,255,0.15);' : '' ?>" onmouseover="if('<?= $sidebarView ?>' !== 'approved') this.style.background='rgba(255,255,255,0.1)'" onmouseout="if('<?= $sidebarView ?>' !== 'approved') this.style.background=''">
                    <i class="fas fa-check-circle text-base opacity-70 group-hover:opacity-100"></i>
                    <span class="sidebar-text">Approved</span>
                    <?php if ($sidebarView === 'approved'): ?>
                        <span class="ml-auto w-1.5 h-1.5 rounded-full" style="background: #4ade80;"></span>
                    <?php endif; ?>
                </a>
                <a href="aar_registrations.php?status=rejected" title="Rejected" class="sidebar-link group flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 hover:translate-x-1 <?= $sidebarView === 'rejected' ? '' : '' ?>" style="<?= $sidebarView === 'rejected' ? 'background: rgba(255,255,255,0.15);' : '' ?>" onmouseover="if('<?= $sidebarView ?>' !== 'rejected') this.style.background='rgba(255,255,255,0.1)'" onmouseout="if('<?= $sidebarView ?>' !== 'rejected') this.style.background=''">
                    <i class="fas fa-times-circle text-base opacity-70 group-hover:opacity-100"></i>
                    <span class="sidebar-text">Rejected</span>
                    <?php if ($sidebarView === 'rejected'): ?>
                        <span class="ml-auto w-1.5 h-1.5 rounded-full" style="background: #f87171;"></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Admin Tools Section -->
        <div class="pt-4 border-t" style="border-color: rgba(255,255,255,0.1);">
            <div class="mb-2 px-3">
                <p class="sidebar-text text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.6);">Admin Tools</p>
            </div>
            <div class="space-y-1">
                <a href="manage_users.php" title="Manage Users" class="sidebar-link group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium transition-all duration-200 hover:translate-x-1" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background=''">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg transition-all" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <span class="sidebar-text">Manage Users</span>
                </a>
                <a href="receipts.php" title="Receipts" class="sidebar-link group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium transition-all duration-200 hover:translate-x-1" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background=''">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg transition-all" style="background: rgba(255,255,255,0.1);" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                        <i class="fas fa-file-invoice-dollar text-lg"></i>
                    </div>
                    <span class="sidebar-text">Receipts</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Logout Section -->
    <div class="px-3 py-4 border-t mt-auto" style="border-color: rgba(255,255,255,0.1);">
        <a href="admin_logout.php" title="Logout" class="sidebar-link group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition-all duration-200 w-40 justify-center" style="background: rgba(220, 38, 38, 0.9);" onmouseover="this.style.background='#b91c1c'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.1)';" onmouseout="this.style.background='rgba(220, 38, 38, 0.9)'; this.style.boxShadow=''">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg" style="background: rgba(255,255,255,0.2);">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </div>
            <span class="sidebar-text text-center">Logout</span>
        </a>
    </div>
    <script>
        function toggleAARMenu() {
            const submenu = document.getElementById('aarSubmenu');
            const caret = document.getElementById('aarCaret');
            submenu.classList.toggle('hidden');
            caret.classList.toggle('rotate-180');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const logo = document.getElementById('sidebarLogo');
            const texts = document.querySelectorAll('.sidebar-text');
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            const aarSubmenu = document.getElementById('aarSubmenu');
            const aarCaret = document.getElementById('aarCaret');
            const sectionHeaders = document.querySelectorAll('nav > div > div.mb-2');
            const logoContainer = logo.parentElement;

            const logoutBtn = document.querySelector('a[title="Logout"]');
            if (sidebar.classList.contains('w-64')) {
                // Collapse sidebar
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                logo.classList.remove('w-16');
                logo.classList.add('w-8');
                logoContainer.classList.remove('p-2');
                logoContainer.classList.add('p-1');
                texts.forEach(t => t.classList.add('hidden'));

                // Center icons and adjust link spacing
                sidebarLinks.forEach(link => {
                    link.classList.remove('gap-3', 'px-3.5', 'gap-2.5', 'px-3', 'justify-between', 'items-start');
                    link.classList.add('justify-center', 'px-0', 'items-center');
                });
                // Center AAR button
                const aarButton = document.querySelector('button[title="AAR Applications"]');
                if (aarButton) {
                    aarButton.classList.remove('justify-between');
                    aarButton.classList.add('justify-center', 'items-center');
                }
                // Collapse logout button layout
                if (logoutBtn) {
                    logoutBtn.classList.remove('w-40', 'gap-3', 'px-3.5', 'justify-center');
                    logoutBtn.classList.add('justify-center', 'px-0', 'items-center');
                    logoutBtn.style.width = '';
                }

                // Hide AAR submenu and caret when collapsed
                aarSubmenu.classList.add('hidden');
                aarCaret.classList.add('hidden');

                // Hide section headers
                sectionHeaders.forEach(h => h.classList.add('hidden'));

                toggleBtn.style.left = '90px';
            } else {
                // Expand sidebar
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                logo.classList.remove('w-8');
                logo.classList.add('w-16');
                logoContainer.classList.remove('p-1');
                logoContainer.classList.add('p-2');
                texts.forEach(t => t.classList.remove('hidden'));

                // Restore normal layout
                sidebarLinks.forEach(link => {
                    link.classList.remove('justify-center', 'px-0', 'items-center');
                    if (link.closest('#aarSubmenu')) {
                        link.classList.add('gap-2.5', 'px-3');
                    } else {
                        link.classList.add('gap-3', 'px-3.5');
                    }
                });
                // Restore AAR button layout
                const aarButton = document.querySelector('button[title="AAR Applications"]');
                if (aarButton) {
                    aarButton.classList.remove('justify-center', 'items-center');
                    aarButton.classList.add('justify-between');
                }
                // Restore logout button layout
                if (logoutBtn) {
                    logoutBtn.classList.remove('justify-center', 'px-0', 'items-center');
                    logoutBtn.classList.add('w-40', 'gap-3', 'px-3.5', 'justify-center');
                    logoutBtn.style.width = '';
                }

                // Show caret
                aarCaret.classList.remove('hidden');

                // Show section headers
                sectionHeaders.forEach(h => h.classList.remove('hidden'));

                toggleBtn.style.left = '270px';
            }
        }
    </script>
</aside>