// logout-handler.js
(function() {
    // 1. Create the Modal HTML
    const modalHTML = `
    <div id="logout-modal" style="position:fixed; inset:0; z-index:10000; display:flex; align-items:center; justify-content:center; visibility:hidden; opacity:0; transition: all 0.3s ease; font-family: sans-serif;">
        <div style="position:absolute; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(8px);"></div>
        <div style="position:relative; background:white; width:90%; max-width:400px; padding:32px; border-radius:24px; text-align:center; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); transform:scale(0.95); transition:0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div style="width:64px; height:64px; background:#fee2e2; color:#ef4444; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:24px;">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h2 style="margin:0 0 8px; color:#1e293b; font-size:1.5rem; font-weight:700;">Sign Out?</h2>
            <p style="color:#64748b; font-size:0.95rem; line-height:1.5; margin-bottom:24px;">Are you sure you want to log out? Any unsaved progress may be lost.</p>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <a href="http://localhost/GCST_Track_System/actions/log_out.php" style="background:#ef4444; color:white; padding:14px; border-radius:12px; font-weight:600; text-decoration:none; transition:0.2s;">Yes, Log Out</a>
                <button id="close-logout" style="background:#f1f5f9; color:#475569; padding:12px; border:none; border-radius:12px; font-weight:600; cursor:pointer; text-align:center;">Stay Logged In</button>
            </div>
        </div>
    </div>`;

    // 2. Inject Modal into Body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    const modal = document.getElementById('logout-modal');
    const content = modal.querySelector('div:last-child');
    const trigger = document.getElementById('logout-trigger');
    const closeBtn = document.getElementById('close-logout');

    // 3. Smooth Toggle Logic
    const toggleModal = (show) => {
        modal.style.visibility = show ? 'visible' : 'hidden';
        modal.style.opacity = show ? '1' : '0';
        content.style.transform = show ? 'scale(1)' : 'scale(0.95)';
        document.body.style.overflow = show ? 'hidden' : 'auto';
    };

    // 4. Event Listeners
    if(trigger) trigger.onclick = () => toggleModal(true);
    if(closeBtn) closeBtn.onclick = () => toggleModal(false);
    
    // Close on ESC key
    window.addEventListener('keydown', (e) => { if(e.key === 'Escape') toggleModal(false); });
})();