export function getSidebarHTML() {
    // Define global utility functions for the sidebar
    if (!window.__GCST_SIDEBAR_INITIALIZED__) {
        window.__GCST_SIDEBAR_INITIALIZED__ = true;

        const isMobile = () => window.matchMedia("(max-width: 1024px)").matches;

        window.logoutUser = function() {
            const modal = document.getElementById('sidebar-logout-modal');
            if (modal) {
                modal.style.display = 'flex';
                void modal.offsetHeight;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeLogoutModal = function() {
            const modal = document.getElementById('sidebar-logout-modal');
            if (!modal) return;
            modal.classList.remove('active');
            document.body.style.overflow = '';
            const onTransitionEnd = (e) => {
                if (e.propertyName === 'opacity' || e.propertyName === 'visibility') {
                    modal.style.display = 'none';
                    modal.removeEventListener('transitionend', onTransitionEnd);
                }
            };
            modal.addEventListener('transitionend', onTransitionEnd);
            setTimeout(() => { if (modal.style.display === 'flex' && !modal.classList.contains('active')) modal.style.display = 'none'; }, 350);
        };

        window.performLogout = function() {
            const toClear = ['sidebar-minimized'];
            toClear.forEach(key => localStorage.removeItem(key));
            sessionStorage.clear(); 
            window.location.replace('/GCST_Track_System/actions/sign_out.php');
        };

        window.handleSidebarLinkClick = function() {
            if (isMobile()) {
                if (typeof window.toggleSidebar === 'function') window.toggleSidebar();
            }
        };

        window.toggleDarkMode = function() {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('admincashier-dark-mode', isDark ? 'true' : 'false');
            const button = document.getElementById('dark-mode-toggle');
            const icon = button?.querySelector('i');
            const text = button?.querySelector('span');
            const label = isDark ? 'Switch to light mode' : 'Switch to dark mode';
            button?.setAttribute('title', label);
            button?.setAttribute('aria-label', label);
            if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            if (text) text.textContent = isDark ? 'Light Mode' : 'Dark Mode';
        };

        window.addEventListener('keydown', (e) => { 
            if (e.key === 'Escape') window.closeLogoutModal(); 
        });

        if (!document.getElementById('sidebar-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'sidebar-overlay';
            overlay.addEventListener('click', () => {
                if (typeof window.toggleSidebar === 'function') {
                    window.toggleSidebar();
                }
            });
            document.body.appendChild(overlay);
        }
    }

    // Ensure the logout modal exists in the body
    if (!document.getElementById('sidebar-logout-modal')) {
        const modalHTML = `
        <div id="sidebar-logout-modal" class="logout-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="logout-title">
            <div class="logout-modal-card">
                <div class="logout-modal-icon"><i class="fas fa-sign-out-alt"></i></div>
                <h2 class="logout-modal-title" id="logout-title">Confirm Logout</h2>
                <p class="logout-modal-text">Are you sure you want to end your session? Make sure all transaction data has been saved.</p>
                <div class="logout-modal-actions">
                    <button onclick="closeLogoutModal()" class="btn-modal btn-modal-cancel">Stay</button>
                    <button onclick="performLogout()" class="btn-modal btn-modal-confirm">Log Out</button>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    return `
<style>
    :root {
        --sidebar-width: 304px;
        --sidebar-min-width: 88px;
        --primary-blue: #4157f3;
        --primary-blue-soft: #f2f3ff;
        --bg-sidebar: #ffffff;
        --text-main: #172644;
        --text-muted: #71809a;
        --border-color: #e9edf5;
        --nav-transition: all 0.25s ease;
        --surface-hover: #f8f9fc;
        --shadow-soft: 10px 0 34px rgba(31, 53, 97, 0.06);
    }

    #main-sidebar.sidebar {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: var(--sidebar-width);
        background: var(--bg-sidebar);
        border-right: 1px solid var(--border-color);
        box-shadow: var(--shadow-soft);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        padding: 1.25rem 1rem 1rem;
        transition: var(--nav-transition);
        overflow-y: auto;
        overflow-x: hidden;
    }

    #main-sidebar.sidebar::-webkit-scrollbar {
        width: 7px;
    }
    #main-sidebar.sidebar::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.35);
        border-radius: 999px;
    }

    @media (min-width: 1025px) {
        #main-sidebar.sidebar.minimized {
            width: var(--sidebar-min-width);
            padding: 1rem 0;
            box-sizing: border-box;
        }
        #main-sidebar.sidebar.minimized .brand-text,
        #main-sidebar.sidebar.minimized .nav-section-label,
        #main-sidebar.sidebar.minimized .sidebar-link span {
            opacity: 0; pointer-events: none; width: 0; margin: 0; display: none;
        }
        #main-sidebar.sidebar.minimized .sidebar-brand {
            justify-content: center;
            padding: 0.5rem 0 1rem;
            margin-bottom: 1rem;
        }
        .sidebar.minimized .brand-content {
            justify-content: center;
        }
        #main-sidebar.sidebar.minimized .sidebar-link {
            justify-content: center;
            width: 100%;
            padding: 0.9rem 0;
            border-radius: 1rem;
        }
        #main-sidebar.sidebar.minimized .sidebar-link i {
            margin: 0;
            font-size: 1.1rem;
        }
    }

    @media (max-width: 1024px) {
        #main-sidebar.sidebar {
            transform: translateX(-110%);
            background: var(--bg-sidebar);
            border-radius: 0 1.5rem 1.5rem 0;
            box-shadow: 18px 0 40px rgba(0,0,0,0.08);
        }
        #main-sidebar.sidebar.active { transform: translateX(0); }
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.9rem;
        margin-bottom: 2rem;
        padding: 0.75rem 0.65rem 1rem;
        position: relative;
        border-radius: 1rem;
        transition: background 0.2s ease;
    }
    
    .brand-content {
        display: flex;
        align-items: center;
        gap: 0.95rem;
        min-width: 0;
        flex: 1;
        padding-top: 0;
    }
    
    .brand-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 0;
        background: transparent;
        border: 0;
        border-radius: 0.9rem;
        cursor: pointer;
    }

    .brand-logo {
        display: block;
        width: 68px;
        height: 68px;
        min-width: 68px;
        min-height: 68px;
        object-fit: contain;
        flex-shrink: 0;
        border-radius: 0.9rem;
        margin-top: 0;
        opacity: 1;
        visibility: visible;
        transition: transform 0.25s ease, filter 0.25s ease;
    }

    .brand-toggle:hover .brand-logo {
        transform: scale(1.06);
        filter: drop-shadow(0 5px 8px rgba(65, 87, 243, 0.16));
    }

    .brand-toggle:focus-visible {
        outline: 3px solid rgba(65, 87, 243, 0.2);
        outline-offset: 4px;
    }

    .sidebar-brand:hover {
        background: rgba(248, 250, 252, 0.75);
    }

    .dark-mode-toggle {
        width: 100%;
        min-height: 2.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.7rem;
        flex-shrink: 0;
        padding: 0.55rem 0.75rem;
        border: 1px solid #e9edf5;
        border-radius: 0.8rem;
        background: #fbfcfe;
        color: #63728b;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .dark-mode-toggle:hover,
    .dark-mode-toggle:focus-visible {
        color: #4157f3;
        background: #f2f3ff;
        border-color: #cdd4ff;
        outline: none;
    }

    .dark-mode-toggle span {
        font-size: 0.78rem;
        font-weight: 700;
    }

    #main-sidebar.sidebar.minimized .sidebar-theme-control {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    #main-sidebar.sidebar.minimized .dark-mode-toggle {
        width: 2.8rem;
        height: 2.8rem;
        padding: 0;
        justify-content: center;
    }

    #main-sidebar.sidebar.minimized .dark-mode-toggle span {
        display: none;
    }
    .sidebar-brand img {
        width: 58px;
        height: 58px;
        object-fit: contain;
        flex-shrink: 0;
    }
    
    /* Shared layout logic for all brand text elements */
    .brand-subtitle,
    .brand-title,
    .brand-role {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        /* Added line-height to ensure text doesn't 'touch' when stacked */
        line-height: 1.3; 
    }

    /* Brand Subtitle - High contrast, small and clean */
    /* Container: Manages layout and spacing */
    .brand-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.22rem;
        padding-top: 0;
        min-width: 0;
        overflow: hidden;
    }

    /* Base styles for all text elements inside the container */
    .brand-text > * {
        margin: 0;
        line-height: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Typography Hierarchy */
    .brand-subtitle {
        font-size: 0.73rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--primary-blue);
    }

    .brand-title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: 0;
    }

    .brand-role {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--primary-blue);
        opacity: 0.9;
    }


    .nav-section-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        margin: 0.95rem 0 0.7rem 0.95rem;
        letter-spacing: 0.1em;
        opacity: 0.82;
    }

    .sidebar-nav {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.72rem 0.8rem;
        border-radius: 1.1rem;
        color: var(--text-muted);
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }
    .sidebar-link i {
        width: 2.8rem;
        height: 2.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 1.15rem;
        color: #63728b;
        background: #fbfcfe;
        border: 1px solid #edf0f5;
        border-radius: 0.9rem;
        flex-shrink: 0;
        transition: var(--nav-transition);
    }
    .sidebar-link:hover {
        background: var(--primary-blue-soft);
        color: var(--primary-blue);
        transform: translateX(2px);
    }
    .sidebar-link:hover i {
        color: var(--primary-blue);
        border-color: #e0e4ff;
        background: #ffffff;
    }
    .sidebar-link:focus-visible {
        outline: none;
        box-shadow: inset 0 0 0 2px rgba(37, 99, 235, 0.12);
    }
    .sidebar-link.active {
        background: var(--primary-blue-soft);
        color: var(--primary-blue);
        box-shadow: none;
    }
    .sidebar-link.active i {
        color: var(--primary-blue);
        background: #ffffff;
        border-color: #e0e4ff;
    }
    .sidebar-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 0.3rem;
        height: 100%;
        background: var(--primary-blue);
        border-radius: 999px;
        transform: translateY(-50%);
    }

    .sidebar-badge.hidden { display: none; }

    .sidebar-footer {
        margin-top: auto;
        padding-top: 1rem;
        margin-left: 0.15rem;
        margin-right: 0.15rem;
        border-top: 1px solid var(--border-color);
    }
    .btn-logout {
        color: #ef4444;
        font-weight: 700;
        border: 1px solid #ffe1e1;
        background: #fffafa;
    }
    .btn-logout:hover {
        background: #fff1f1;
        color: #dc2626;
        transform: translateX(2px);
    }
    .btn-logout i {
        color: #ef4444;
        background: #fffafa;
        border-color: #ffe4e4;
    }

    .logout-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .logout-modal-overlay.active { display: flex; opacity: 1; }

    .logout-modal-card {
        background: #ffffff;
        width: min(92%, 420px);
        padding: 2rem 1.75rem;
        border-radius: 1.25rem;
        text-align: center;
        box-shadow: 0 24px 50px -18px rgba(15, 23, 42, 0.4);
        transform: translateY(14px);
        transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .logout-modal-overlay.active .logout-modal-card { transform: translateY(0); }

    .logout-modal-icon {
        width: 64px;
        height: 64px;
        background: #fff1f2;
        color: #e11d48;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        margin: 0 auto 1.15rem;
    }

    .logout-modal-title {
        font-weight: 700;
        font-size: 1.4rem;
        color: var(--text-main);
        margin-bottom: 0.65rem;
    }
    .logout-modal-text {
        font-size: 0.95rem;
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .logout-modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
    }
    .btn-modal {
        padding: 0.85rem;
        border-radius: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-modal:hover {
        transform: translateY(-1px);
    }
    .btn-modal-cancel {
        background: #f1f5f9;
        color: #475569;
    }
    .btn-modal-confirm {
        background: #e11d48;
        color: #ffffff;
    }

    #sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.18);
        z-index: 999;
        display: none;
        transition: opacity 0.2s ease;
    }
    #sidebar-overlay.active { display: block; }

    body.dark-mode #main-sidebar.sidebar {
        background: #111827 !important;
        border-color: #273449 !important;
        box-shadow: 10px 0 34px rgba(2, 6, 23, 0.28) !important;
    }

    body.dark-mode #main-sidebar .brand-subtitle,
    body.dark-mode #main-sidebar .brand-role {
        color: #93a8ff !important;
        opacity: 1 !important;
    }

    body.dark-mode #main-sidebar .brand-title {
        color: #ffffff !important;
    }

    body.dark-mode #main-sidebar .sidebar-brand,
    body.dark-mode #main-sidebar .brand-content,
    body.dark-mode #main-sidebar .brand-text {
        opacity: 1 !important;
        visibility: visible !important;
    }

    body.dark-mode #main-sidebar .brand-logo,
    body.dark-mode #main-sidebar .sidebar-brand img {
        display: block !important;
        width: 58px !important;
        height: 58px !important;
        min-width: 58px !important;
        min-height: 58px !important;
        opacity: 1 !important;
        visibility: visible !important;
        object-fit: contain !important;
        background: #ffffff !important;
    }

    body.dark-mode #main-sidebar .brand-logo {
        filter: drop-shadow(0 4px 8px rgba(129, 140, 248, 0.28));
    }

    body.dark-mode .logout-modal-card {
        background: #172033 !important;
        color: #e5edf8 !important;
        border: 1px solid #334155;
    }

    body.dark-mode .logout-modal-title {
        color: #f8fafc !important;
    }

    body.dark-mode .logout-modal-text {
        color: #cbd5e1 !important;
    }

    body.dark-mode .btn-modal-cancel {
        background: #334155 !important;
        color: #f1f5f9 !important;
    }

    body.dark-mode .sidebar-brand:hover,
    body.dark-mode .sidebar-link:hover {
        background: #1e293b !important;
    }

    body.dark-mode .sidebar-link {
        color: #a9b7cb !important;
    }

    body.dark-mode .sidebar-link i,
    body.dark-mode .dark-mode-toggle {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #a9b7cb !important;
    }

    body.dark-mode .sidebar-link.active {
        background: #26345b !important;
        color: #ffffff !important;
    }

    body.dark-mode .sidebar-link.active i {
        background: #334155 !important;
        border-color: #5968e8 !important;
        color: #b8c3ff !important;
    }

    body.dark-mode .sidebar-footer {
        border-color: #273449 !important;
    }

    body.dark-mode .sidebar-link.btn-logout {
        background: #241b24 !important;
        border-color: #4c2631 !important;
        color: #fda4af !important;
    }

    /* Loaded with the shared sidebar so this wins over page-specific inline styles. */
    body.dark-mode .content-wrapper .panel,
    body.dark-mode .content-wrapper .card,
    body.dark-mode .content-wrapper .history-card,
    body.dark-mode .content-wrapper .logs-section,
    body.dark-mode .content-wrapper .queue-card,
    body.dark-mode .content-wrapper .inventory-board,
    body.dark-mode .content-wrapper .panel-card,
    body.dark-mode .content-wrapper .inventory-panel,
    body.dark-mode .content-wrapper .queue-list > *,
    body.dark-mode .content-wrapper .active-queue,
    body.dark-mode .content-wrapper .queue-item {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .products-grid,
    body.dark-mode .content-wrapper .product-card,
    body.dark-mode .content-wrapper .product-body,
    body.dark-mode .content-wrapper .product-actions,
    body.dark-mode .content-wrapper .inventory-grid,
    body.dark-mode .content-wrapper .inventory-main,
    body.dark-mode .content-wrapper .logs-toolbar,
    body.dark-mode .content-wrapper .table-scroll-container,
    body.dark-mode .content-wrapper .queue-dashboard,
    body.dark-mode .content-wrapper .empty-state {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper table,
    body.dark-mode .content-wrapper table th,
    body.dark-mode .content-wrapper table td {
        background: #172033 !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .content-wrapper input,
    body.dark-mode .content-wrapper select,
    body.dark-mode .content-wrapper textarea,
    body.dark-mode .content-wrapper .search-input {
        background: #0f172a !important;
        color: #e5edf8 !important;
        border-color: #475569 !important;
    }

    body.dark-mode .content-wrapper .pagination-controls,
    body.dark-mode .content-wrapper .logs-pagination,
    body.dark-mode .content-wrapper .pagination-info,
    body.dark-mode .content-wrapper .logs-pagination-info {
        background: transparent !important;
        color: #a9b7cb !important;
    }

    body.dark-mode .content-wrapper .pagination-controls button,
    body.dark-mode .content-wrapper .logs-pagination button,
    body.dark-mode .content-wrapper .pagination-btn {
        background: #1e293b !important;
        color: #dbe5f2 !important;
        border-color: #475569 !important;
    }

    body.dark-mode .content-wrapper .pagination-controls button.active,
    body.dark-mode .content-wrapper .logs-pagination button.active,
    body.dark-mode .content-wrapper .pagination-btn.active {
        background: #4f46e5 !important;
        color: #ffffff !important;
        border-color: #6366f1 !important;
    }

    body.dark-mode .content-wrapper .bg-white,
    body.dark-mode .content-wrapper .bg-gray-50,
    body.dark-mode .content-wrapper .bg-gray-100,
    body.dark-mode .content-wrapper .bg-indigo-50,
    body.dark-mode .content-wrapper .bg-purple-50 {
        background: #1e293b !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .inventory-card,
    body.dark-mode .content-wrapper .inventory-card-body,
    body.dark-mode .content-wrapper .product-preview,
    body.dark-mode .content-wrapper .quick-note,
    body.dark-mode .content-wrapper .toast,
    body.dark-mode .content-wrapper .delete-modal-panel {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .card-image-wrapper,
    body.dark-mode .content-wrapper .image-preview,
    body.dark-mode .content-wrapper .filter-pill,
    body.dark-mode .content-wrapper .btn-close-details,
    body.dark-mode .content-wrapper .delete-modal-preview,
    body.dark-mode .content-wrapper .delete-modal-close {
        background: #1e293b !important;
        border-color: #475569 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .filter-pill.active {
        background: #4f46e5 !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
    }

    body.dark-mode .content-wrapper .inventory-card-title,
    body.dark-mode .content-wrapper .inventory-card-meta,
    body.dark-mode .content-wrapper .card-stock-info,
    body.dark-mode .content-wrapper .toast-content,
    body.dark-mode .content-wrapper .delete-modal-header h3,
    body.dark-mode .content-wrapper .delete-modal-message strong,
    body.dark-mode .content-wrapper .delete-modal-preview-row strong {
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .inventory-card-meta,
    body.dark-mode .content-wrapper .stock-label,
    body.dark-mode .content-wrapper .delete-modal-message,
    body.dark-mode .content-wrapper .delete-modal-preview-row span:first-child {
        color: #a9b7cb !important;
    }

    body.dark-mode .content-wrapper .add-product-form input,
    body.dark-mode .content-wrapper .add-product-form select,
    body.dark-mode .content-wrapper .add-product-form textarea,
    body.dark-mode .content-wrapper .product-details input,
    body.dark-mode .content-wrapper .product-details select,
    body.dark-mode .content-wrapper .product-details textarea,
    body.dark-mode .content-wrapper .form-group input,
    body.dark-mode .content-wrapper .form-group select {
        background: #0f172a !important;
        border-color: #475569 !important;
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .form-section-divider,
    body.dark-mode .content-wrapper .panel-card-header,
    body.dark-mode .content-wrapper .product-info-header {
        border-color: #334155 !important;
        color: #b8c3ff !important;
    }

    body.dark-mode .content-wrapper .queue-stat-card,
    body.dark-mode .content-wrapper .queue-card,
    body.dark-mode .content-wrapper .queue-dashboard,
    body.dark-mode .content-wrapper .queue-item,
    body.dark-mode .content-wrapper .ticket-preview-panel,
    body.dark-mode .content-wrapper .ticket-preview-card,
    body.dark-mode .content-wrapper .kiosk-window-card,
    body.dark-mode .content-wrapper .kiosk-next-item,
    body.dark-mode .content-wrapper #reassign-modal > div,
    body.dark-mode .content-wrapper #remove-ticket-modal > div {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .queue-item {
        background: #1e293b !important;
    }

    body.dark-mode .content-wrapper .queue-number,
    body.dark-mode .content-wrapper .queue-details .text-gray-800,
    body.dark-mode .content-wrapper .ticket-number,
    body.dark-mode .content-wrapper .kiosk-window-number,
    body.dark-mode .content-wrapper .kiosk-next-name,
    body.dark-mode .content-wrapper .kiosk-window-name {
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .queue-status,
    body.dark-mode .content-wrapper .ticket-info,
    body.dark-mode .content-wrapper .kiosk-window-label,
    body.dark-mode .content-wrapper .kiosk-date {
        color: #a9b7cb !important;
    }

    body.dark-mode .content-wrapper .queue-stat-label,
    body.dark-mode .content-wrapper .queue-stat-note {
        color: #a9b7cb !important;
    }

    body.dark-mode .content-wrapper .bg-white,
    body.dark-mode .content-wrapper .bg-gray-50,
    body.dark-mode .content-wrapper .bg-gray-100,
    body.dark-mode .content-wrapper .bg-indigo-50,
    body.dark-mode .content-wrapper .bg-purple-50,
    body.dark-mode .content-wrapper .bg-purple-100,
    body.dark-mode .content-wrapper .kiosk-overlay {
        background: #111827 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .kiosk-header,
    body.dark-mode .content-wrapper .kiosk-next-item,
    body.dark-mode .content-wrapper #remove-ticket-modal > div > div,
    body.dark-mode .content-wrapper #reassign-modal > div > div {
        border-color: #334155 !important;
    }

    body.dark-mode .content-wrapper .kiosk-window-name,
    body.dark-mode .content-wrapper .kiosk-next-item {
        background: #1e293b !important;
    }

    body.dark-mode .content-wrapper .exit-kiosk {
        background: #1e293b !important;
        color: #dbe5f2 !important;
        border-color: #475569 !important;
    }

    /* Cashier page has extensive inline light styles; normalize its work surfaces here. */
    body.dark-mode .content-wrapper .cashier-layout > .panel,
    body.dark-mode .content-wrapper .cashier-sidebar > .panel,
    body.dark-mode .content-wrapper .management-section > .panel,
    body.dark-mode .content-wrapper .receipt-modal-card,
    body.dark-mode .content-wrapper .receipt-review-modal-panel,
    body.dark-mode .content-wrapper .confirmation-modal-card,
    body.dark-mode .content-wrapper #checkout-modal > .panel,
    body.dark-mode .content-wrapper #view-txn-modal > .panel,
    body.dark-mode .content-wrapper #voided-report-modal > .panel,
    body.dark-mode .content-wrapper #partial-return-modal > .panel {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child > .panel-header,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child > .search-group,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child > .filter-group,
    body.dark-mode .content-wrapper .management-section > .panel:first-child .panel-header,
    body.dark-mode .content-wrapper #txn-history-content,
    body.dark-mode .content-wrapper #cart-footer,
    body.dark-mode .content-wrapper .cart-item,
    body.dark-mode .content-wrapper .qty-control,
    body.dark-mode .content-wrapper .summary-row[style*="background"],
    body.dark-mode .content-wrapper .receipt-mode-switch,
    body.dark-mode .content-wrapper .receipt-signature-preview,
    body.dark-mode .content-wrapper .official-signature-seal,
    body.dark-mode .content-wrapper .receipt-hint {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .product-body,
    body.dark-mode .content-wrapper .product-actions,
    body.dark-mode .content-wrapper .product-metadata-section,
    body.dark-mode .content-wrapper .cart-thumb,
    body.dark-mode .content-wrapper .receipt-card,
    body.dark-mode .content-wrapper .checkout-section,
    body.dark-mode .content-wrapper .receipt-panel,
    body.dark-mode .content-wrapper .financial-card,
    body.dark-mode .content-wrapper .receipt-preview-card,
    body.dark-mode .content-wrapper .receipt-preview-header-shell,
    body.dark-mode .content-wrapper .receipt-preview-seal,
    body.dark-mode .content-wrapper .receipt-loading-shell,
    body.dark-mode .content-wrapper .confirmation-modal-body,
    body.dark-mode .content-wrapper .confirmation-modal-actions {
        background: #172033 !important;
        border-color: #334155 !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .receipt-input,
    body.dark-mode .content-wrapper .receipt-select,
    body.dark-mode .content-wrapper .receipt-textarea,
    body.dark-mode .content-wrapper .catalog-course-filter,
    body.dark-mode .content-wrapper .catalog-sort-select,
    body.dark-mode .content-wrapper #cart-content input,
    body.dark-mode .content-wrapper #checkout-modal input,
    body.dark-mode .content-wrapper #checkout-modal select,
    body.dark-mode .content-wrapper #checkout-modal textarea {
        background: #0f172a !important;
        border-color: #475569 !important;
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .cart-name,
    body.dark-mode .content-wrapper .cart-item-info h4,
    body.dark-mode .content-wrapper .cart-subtotal,
    body.dark-mode .content-wrapper .receipt-preview-header-title,
    body.dark-mode .content-wrapper .receipt-preview-seal-title,
    body.dark-mode .content-wrapper .confirmation-modal-title,
    body.dark-mode .content-wrapper .confirmation-modal-message,
    body.dark-mode .content-wrapper #student-name-text,
    body.dark-mode .content-wrapper .management-section .panel-header h2 {
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .cart-item-info p,
    body.dark-mode .content-wrapper .cart-header-meta,
    body.dark-mode .content-wrapper .receipt-preview-header-subtitle,
    body.dark-mode .content-wrapper .receipt-line,
    body.dark-mode .content-wrapper .checkout-label,
    body.dark-mode .content-wrapper .confirmation-modal-message {
        color: #a9b7cb !important;
    }

    body.dark-mode .content-wrapper .pagination-controls,
    body.dark-mode .content-wrapper #voided-report-pagination {
        background: transparent !important;
        border-color: #334155 !important;
    }

    /* Catch remaining inline and Tailwind light surfaces on every Admin Cashier page. */
    body.dark-mode .content-wrapper [class~="bg-white"],
    body.dark-mode .content-wrapper [class~="bg-gray-50"],
    body.dark-mode .content-wrapper [class~="bg-gray-100"],
    body.dark-mode .content-wrapper [style*="background: #ffffff"],
    body.dark-mode .content-wrapper [style*="background:#ffffff"],
    body.dark-mode .content-wrapper [style*="background: #fff"],
    body.dark-mode .content-wrapper [style*="background:#fff"],
    body.dark-mode .content-wrapper [style*="background: #f8fafc"],
    body.dark-mode .content-wrapper [style*="background:#f8fafc"] {
        background: #1e293b !important;
        color: #dbe5f2 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .content-wrapper [style*="background: linear-gradient(135deg, #ffffff"],
    body.dark-mode .content-wrapper [style*="background:linear-gradient(135deg, #ffffff"],
    body.dark-mode .content-wrapper [style*="background: linear-gradient(90deg, #f8fafc"],
    body.dark-mode .content-wrapper [style*="background:linear-gradient(90deg, #f8fafc"] {
        background: #172033 !important;
        color: #dbe5f2 !important;
        border-color: #334155 !important;
    }

    body.dark-mode .content-wrapper .btn-primary,
    body.dark-mode .content-wrapper .btn-danger,
    body.dark-mode .content-wrapper .add-to-cart-btn,
    body.dark-mode .content-wrapper .action-btn,
    body.dark-mode .content-wrapper .queue-btn,
    body.dark-mode .content-wrapper .status-pill,
    body.dark-mode .content-wrapper .status-badge,
    body.dark-mode .content-wrapper .low-stock-pill {
        color: #ffffff !important;
    }

    body.dark-mode .content-wrapper .btn-primary,
    body.dark-mode .content-wrapper .add-to-cart-btn,
    body.dark-mode .content-wrapper .action-btn {
        background: #4f46e5 !important;
        border-color: #6366f1 !important;
    }

    body.dark-mode .content-wrapper .btn-danger,
    body.dark-mode .content-wrapper .btn-remove,
    body.dark-mode .content-wrapper .remove-btn {
        background: #dc2626 !important;
        border-color: #ef4444 !important;
    }

    body.dark-mode .content-wrapper [style*="#f8fbff"],
    body.dark-mode .content-wrapper [style*="#f7faff"],
    body.dark-mode .content-wrapper [style*="#fbfdff"],
    body.dark-mode .content-wrapper [style*="#f1f5f9"],
    body.dark-mode .content-wrapper [style*="#eef2ff"],
    body.dark-mode .content-wrapper [style*="#eff6ff"],
    body.dark-mode .content-wrapper [style*="#ecfdf5"],
    body.dark-mode .content-wrapper [style*="#f0fdf4"],
    body.dark-mode .content-wrapper [style*="#fffbeb"],
    body.dark-mode .content-wrapper [style*="#fff7ed"],
    body.dark-mode .content-wrapper [style*="#fff1f2"],
    body.dark-mode .content-wrapper [style*="#f9fafb"] {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    body.dark-mode .content-wrapper [style*="color: #0f172a"],
    body.dark-mode .content-wrapper [style*="color:#0f172a"],
    body.dark-mode .content-wrapper [style*="color: #111827"],
    body.dark-mode .content-wrapper [style*="color:#111827"],
    body.dark-mode .content-wrapper [style*="color: #172033"],
    body.dark-mode .content-wrapper [style*="color: #334155"],
    body.dark-mode .content-wrapper [style*="color:#334155"],
    body.dark-mode .content-wrapper [style*="color: #475569"],
    body.dark-mode .content-wrapper [style*="color:#475569"],
    body.dark-mode .content-wrapper [style*="color: #64748b"],
    body.dark-mode .content-wrapper [style*="color:#64748b"] {
        color: #cbd5e1 !important;
    }

    body.dark-mode .content-wrapper .low-stock-pill,
    body.dark-mode .content-wrapper .status-badge,
    body.dark-mode .content-wrapper .status-pill,
    body.dark-mode .content-wrapper .stock-badge,
    body.dark-mode .content-wrapper .btn-primary,
    body.dark-mode .content-wrapper .btn-danger,
    body.dark-mode .content-wrapper .btn-remove,
    body.dark-mode .content-wrapper .queue-btn,
    body.dark-mode .content-wrapper .action-btn {
        color: #ffffff !important;
    }

    /* Final cashier detail fallback for nested inline receipt and checkout blocks. */
    body.dark-mode .content-wrapper main [style*="background"]:not(.btn-primary):not(.btn-danger):not(.add-to-cart-btn):not(.receipt-countdown-icon):not(.receipt-countdown-progress):not(#scan-success-overlay):not(#scan-error-overlay) {
        background: #1e293b !important;
        border-color: #334155 !important;
    }

    body.dark-mode .content-wrapper main [style*="color: #0f172a"],
    body.dark-mode .content-wrapper main [style*="color:#0f172a"],
    body.dark-mode .content-wrapper main [style*="color: #111827"],
    body.dark-mode .content-wrapper main [style*="color:#111827"],
    body.dark-mode .content-wrapper main [style*="color: #334155"],
    body.dark-mode .content-wrapper main [style*="color:#334155"],
    body.dark-mode .content-wrapper main [style*="color: #475569"],
    body.dark-mode .content-wrapper main [style*="color:#475569"] {
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper main .receipt-countdown-icon,
    body.dark-mode .content-wrapper main .receipt-countdown-progress,
    body.dark-mode .content-wrapper main #scan-success-overlay,
    body.dark-mode .content-wrapper main #scan-error-overlay,
    body.dark-mode .content-wrapper main .btn-primary,
    body.dark-mode .content-wrapper main .btn-danger,
    body.dark-mode .content-wrapper main .add-to-cart-btn {
        border-color: initial !important;
    }

    /* Cashier dark-mode contrast fixes. */
    body.dark-mode .content-wrapper .panel-header h2,
    body.dark-mode .content-wrapper .panel-header .text-gray-500,
    body.dark-mode .content-wrapper .panel-count,
    body.dark-mode .content-wrapper .product-title,
    body.dark-mode .content-wrapper .product-body h3,
    body.dark-mode .content-wrapper .cart-name,
    body.dark-mode .content-wrapper .cart-item-info h4,
    body.dark-mode .content-wrapper .cart-subtotal,
    body.dark-mode .content-wrapper .management-section h2 {
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .product-category,
    body.dark-mode .content-wrapper .compact-meta,
    body.dark-mode .content-wrapper .product-metadata-section,
    body.dark-mode .content-wrapper .cart-subtitle,
    body.dark-mode .content-wrapper .cart-header-meta,
    body.dark-mode .content-wrapper .catalog-sort-label,
    body.dark-mode .content-wrapper .pagination-ellipsis {
        color: #a9b7cb !important;
    }

    body.dark-mode .content-wrapper .product-metadata-section {
        background: #243149 !important;
        border-left-color: #5268a8 !important;
    }

    body.dark-mode .content-wrapper .catalog-view-toggle,
    body.dark-mode .content-wrapper .catalog-course-filter,
    body.dark-mode .content-wrapper .catalog-sort-select,
    body.dark-mode .content-wrapper .filter-btn,
    body.dark-mode .content-wrapper .btn-secondary,
    body.dark-mode .content-wrapper .qty-control {
        background: #1e293b !important;
        border-color: #53627a !important;
        color: #dbe5f2 !important;
    }

    body.dark-mode .content-wrapper .filter-btn.active,
    body.dark-mode .content-wrapper .catalog-view-btn.active {
        background: #4f46e5 !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
    }

    body.dark-mode .content-wrapper .cart-item {
        background: #243149 !important;
        border-color: #3b4b65 !important;
    }

    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child .panel-header h2,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child .panel-count,
    body.dark-mode .content-wrapper .cashier-sidebar .panel-header h2 {
        color: #e5edf8 !important;
    }

    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child .search-input,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child .catalog-course-filter {
        border-color: #53627a !important;
        color: #e5edf8 !important;
    }

    /* The Cashier page uses #product-grid; keep its late inline scrollbar rules dark. */
    body.dark-mode .content-wrapper #product-grid,
    body.dark-mode .content-wrapper #cart-content,
    body.dark-mode .content-wrapper #txn-history-content {
        scrollbar-color: #64748b #111827 !important;
    }

    body.dark-mode .content-wrapper #product-grid::-webkit-scrollbar,
    body.dark-mode .content-wrapper #cart-content::-webkit-scrollbar,
    body.dark-mode .content-wrapper #txn-history-content::-webkit-scrollbar {
        width: 8px !important;
    }

    body.dark-mode .content-wrapper #product-grid::-webkit-scrollbar-track,
    body.dark-mode .content-wrapper #cart-content::-webkit-scrollbar-track,
    body.dark-mode .content-wrapper #txn-history-content::-webkit-scrollbar-track {
        background: #111827 !important;
        border-radius: 999px !important;
    }

    body.dark-mode .content-wrapper #product-grid::-webkit-scrollbar-thumb,
    body.dark-mode .content-wrapper #cart-content::-webkit-scrollbar-thumb,
    body.dark-mode .content-wrapper #txn-history-content::-webkit-scrollbar-thumb {
        background: #475569 !important;
        border: 2px solid #111827 !important;
        border-radius: 999px !important;
    }

    body.dark-mode .content-wrapper #cart-footer,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child > .panel-header,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child > .search-group,
    body.dark-mode .content-wrapper .cashier-layout > .panel:first-child > .filter-group {
        background: #172033 !important;
        border-color: #334155 !important;
        box-shadow: none !important;
    }

    body.dark-mode .content-wrapper .management-section > .panel:first-child #txn-history-content {
        background: #172033 !important;
        border-color: #334155 !important;
    }
</style>

<aside id="main-sidebar" class="sidebar" aria-label="Main Sidebar">
    <div class="sidebar-brand" id="sidebar-brand-area">
        <div class="brand-content">
            <button onclick="toggleMinimizeSidebar()" id="brand-toggle" class="brand-toggle" title="Collapse sidebar" aria-label="Collapse sidebar" type="button">
                <img src="/GCST_Track_System/assets/images/icons/granby_logo.png" alt="Granby Colleges Logo" class="brand-logo">
            </button>
            <div class="brand-text">
                <span class="brand-subtitle">Granby Colleges of</span>
                <h2 class="brand-title">Science & Technology</h2>
                <span class="brand-role">System Cashier Admin</span>
            </div>
        </div>
    </div>
    <div class="sidebar-theme-control">
        <button type="button" id="dark-mode-toggle" class="dark-mode-toggle" title="Switch to dark mode" aria-label="Switch to dark mode">
            <i class="fas fa-moon" aria-hidden="true"></i><span>Dark Mode</span>
        </button>
    </div>

    <p class="nav-section-label">Main Menu</p>
    <nav class="sidebar-nav">
        <a href="/GCST_Track_System/pages/admincashier/admincashier_dashb.php" class="sidebar-link" title="Dashboard" onclick="handleSidebarLinkClick()">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
        <a href="/GCST_Track_System/pages/admincashier/admincashier_cashier.php" class="sidebar-link" title="Cashier" onclick="handleSidebarLinkClick()">
            <i class="fas fa-cash-register"></i> <span>Cashier</span>
        </a>
        <a href="/GCST_Track_System/pages/admincashier/admincashier_sale.php" class="sidebar-link" title="Sales Report" onclick="handleSidebarLinkClick()">
            <i class="fas fa-chart-line"></i> <span>Sales Report</span>
        </a>
        <a href="/GCST_Track_System/pages/admincashier/admincashier_inventorys.php" class="sidebar-link" title="Inventory" onclick="handleSidebarLinkClick()">
            <i class="fas fa-boxes"></i> <span>Inventory</span>
        </a>
        
        <a href="/GCST_Track_System/pages/admincashier/admincashier_queuing_system.php" class="sidebar-link" title="Queuing System" onclick="handleSidebarLinkClick()">
            <i class="fas fa-users-cog"></i> <span>Queuing System</span>
        </a>
        <a href="/GCST_Track_System/pages/admincashier/admincashier_gmail_notification.php" id="sidebar-gmail-link" class="sidebar-link" title="Gmail Notification" onclick="handleSidebarLinkClick()">
            <i class="fas fa-envelope"></i> <span>Gmail Notification</span>
        </a>
        
        <p class="nav-section-label">Account</p>
        <a href="/GCST_Track_System/pages/admincashier/admincashier_profile.php" class="sidebar-link" title="Profile Settings" onclick="handleSidebarLinkClick()">
            <i class="fas fa-user-circle"></i> <span>Profile Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="javascript:void(0)" onclick="logoutUser()" id="sidebar-logout" class="sidebar-link btn-logout" title="Sign Out">
            <i class="fas fa-sign-out-alt"></i> <span>Sign Out</span>
        </a>
    </div>
</aside>
`;
}