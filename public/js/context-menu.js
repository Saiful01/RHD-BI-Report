/**
 * Global Context Menu System
 * Provides custom right-click menus for navigation items
 */
(function() {
    'use strict';

    let menuElement = null;
    let overlayElement = null;

    // Create elements immediately
    function createElements() {
        // Menu container
        menuElement = document.createElement('div');
        menuElement.className = 'context-menu';
        menuElement.id = 'contextMenu';

        // Overlay
        overlayElement = document.createElement('div');
        overlayElement.className = 'context-menu-overlay';
        overlayElement.style.display = 'none';

        document.body.appendChild(overlayElement);
        document.body.appendChild(menuElement);

        // Overlay events
        overlayElement.oncontextmenu = function(e) {
            e.preventDefault();
            e.stopPropagation();
            hideMenu();
            return false;
        };
        overlayElement.onclick = hideMenu;
    }

    function showMenu(e, navLink) {
        const href = navLink.getAttribute('href');
        const isLogout = navLink.classList.contains('fluent-nav-link-logout');
        const textEl = navLink.querySelector('.fluent-nav-text');
        const text = textEl ? textEl.textContent.trim() : 'Link';
        const isActive = navLink.classList.contains('active');

        let html = '<div class="context-menu-header">' + text + '</div>';

        if (!isLogout && href && href !== '#') {
            html += `
                <div class="context-menu-item primary" data-action="open">
                    <i class="ri-arrow-right-line"></i>
                    <span>Open</span>
                </div>
                <div class="context-menu-item" data-action="newtab">
                    <i class="ri-external-link-line"></i>
                    <span>Open in New Tab</span>
                    <span class="shortcut">Ctrl+Click</span>
                </div>
                <div class="context-menu-item" data-action="copy">
                    <i class="ri-link"></i>
                    <span>Copy Link</span>
                </div>
            `;

            if (isActive) {
                html += `
                    <div class="context-menu-divider"></div>
                    <div class="context-menu-item" data-action="refresh">
                        <i class="ri-refresh-line"></i>
                        <span>Refresh Page</span>
                        <span class="shortcut">F5</span>
                    </div>
                `;
            }
        }

        if (isLogout) {
            html += `
                <div class="context-menu-item danger" data-action="logout">
                    <i class="ri-logout-box-line"></i>
                    <span>Sign Out</span>
                </div>
            `;
        }

        menuElement.innerHTML = html;

        // Bind actions
        menuElement.querySelectorAll('.context-menu-item').forEach(function(item) {
            item.onclick = function() {
                const action = this.getAttribute('data-action');
                hideMenu();

                switch(action) {
                    case 'open':
                        window.location.href = href;
                        break;
                    case 'newtab':
                        window.open(href, '_blank');
                        break;
                    case 'copy':
                        const fullUrl = new URL(href, window.location.origin).href;
                        navigator.clipboard.writeText(fullUrl).then(function() {
                            showToast('Link copied!');
                        });
                        break;
                    case 'refresh':
                        window.location.reload();
                        break;
                    case 'logout':
                        const form = document.getElementById('logoutform');
                        if (form) form.submit();
                        break;
                }
            };
        });

        // Position menu
        let x = e.clientX;
        let y = e.clientY;

        menuElement.style.display = 'block';
        menuElement.style.visibility = 'hidden';

        const menuWidth = menuElement.offsetWidth;
        const menuHeight = menuElement.offsetHeight;

        if (x + menuWidth > window.innerWidth - 10) {
            x = e.clientX - menuWidth;
        }
        if (y + menuHeight > window.innerHeight - 10) {
            y = e.clientY - menuHeight;
        }

        x = Math.max(10, x);
        y = Math.max(10, y);

        menuElement.style.left = x + 'px';
        menuElement.style.top = y + 'px';
        menuElement.style.visibility = 'visible';

        overlayElement.style.display = 'block';

        requestAnimationFrame(function() {
            menuElement.classList.add('visible');
        });
    }

    function hideMenu() {
        if (!menuElement) return;
        menuElement.classList.remove('visible');
        overlayElement.style.display = 'none';
        setTimeout(function() {
            menuElement.style.display = 'none';
        }, 150);
    }

    function showToast(msg) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#107c10;color:#fff;padding:12px 20px;border-radius:6px;z-index:10002;font-size:13px;box-shadow:0 4px 12px rgba(0,0,0,.15);animation:toastIn .3s ease';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 2500);
    }

    // Main initialization
    function init() {
        createElements();

        // Disable context menu on entire sidebar
        document.addEventListener('contextmenu', function(e) {
            const sidebar = e.target.closest('.fluent-sidebar');

            if (sidebar) {
                e.preventDefault();
                e.stopPropagation();

                const navLink = e.target.closest('.fluent-nav-link');
                if (navLink) {
                    showMenu(e, navLink);
                } else {
                    hideMenu();
                }

                return false;
            }

            if (!e.target.closest('.context-menu')) {
                hideMenu();
            }
        }, true);

        // Close on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideMenu();
        });

        // Close on scroll/resize
        window.addEventListener('scroll', hideMenu, true);
        window.addEventListener('resize', hideMenu);
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Also attach directly to sidebar when it exists
    const checkSidebar = setInterval(function() {
        const sidebar = document.querySelector('.fluent-sidebar');
        if (sidebar) {
            clearInterval(checkSidebar);
            sidebar.oncontextmenu = function(e) {
                e.preventDefault();
                e.stopPropagation();

                const navLink = e.target.closest('.fluent-nav-link');
                if (navLink) {
                    showMenu(e, navLink);
                }

                return false;
            };
        }
    }, 100);

    // Expose globally
    window.ContextMenu = { hide: hideMenu, toast: showToast };
})();
