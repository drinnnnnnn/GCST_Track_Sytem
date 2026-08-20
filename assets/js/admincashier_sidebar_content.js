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
            padding: 1rem 0.6rem;
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
            padding: 0.9rem;
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
            background: #ffffff;
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
        width: 58px;
        height: 58px;
        object-fit: contain;
        flex-shrink: 0;
        border-radius: 0.9rem;
        margin-top: 0;
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