/**
 * Global Context Menu System
 * Provides custom right-click menus for navigation items
 */
(function() {
    'use strict';

    // Context menu container
    let menuElement = null;
    let overlayElement = null;
    let currentTarget = null;

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        createMenuElement();
        createOverlay();
        bindEvents();
    }

    function createMenuElement() {
        menuElement = document.createElement('div');
        menuElement.className = 'context-menu';
        menuElement.id = 'contextMenu';
        document.body.appendChild(menuElement);
    }

    function createOverlay() {
        overlayElement = document.createElement('div');
        overlayElement.className = 'context-menu-overlay';
        overlayElement.style.display = 'none';
        document.body.appendChild(overlayElement);
    }

    function bindEvents() {
        // Right-click on nav links
        document.addEventListener('contextmenu', handleContextMenu);

        // Close menu on overlay click
        overlayElement.addEventListener('click', hideMenu);
        overlayElement.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            hideMenu();
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideMenu();
        });

        // Close on scroll
        document.addEventListener('scroll', hideMenu, true);

        // Close on window resize
        window.addEventListener('resize', hideMenu);
    }

    function handleContextMenu(e) {
        const navLink = e.target.closest('.fluent-nav-link');

        if (navLink) {
            e.preventDefault();
            currentTarget = navLink;
            showNavMenu(e, navLink);
            return;
        }

        // If clicked elsewhere, hide menu
        if (!e.target.closest('.context-menu')) {
            hideMenu();
        }
    }

    function showNavMenu(e, navLink) {
        const href = navLink.getAttribute('href');
        const isLogout = navLink.classList.contains('fluent-nav-link-logout');
        const text = navLink.querySelector('.fluent-nav-text')?.textContent || 'Link';
        const isActive = navLink.classList.contains('active');

        let menuItems = [];

        // Header
        menuItems.push({
            type: 'header',
            text: text
        });

        if (!isLogout && href && href !== '#') {
            // Open option
            menuItems.push({
                icon: 'ri-arrow-right-line',
                text: 'Open',
                action: () => window.location.href = href,
                primary: true
            });

            // Open in new tab
            menuItems.push({
                icon: 'ri-external-link-line',
                text: 'Open in New Tab',
                shortcut: 'Ctrl+Click',
                action: () => window.open(href, '_blank')
            });

            // Copy link
            menuItems.push({
                icon: 'ri-link',
                text: 'Copy Link',
                action: () => copyToClipboard(href)
            });

            // Divider
            menuItems.push({ type: 'divider' });

            // Refresh if on same page
            if (isActive) {
                menuItems.push({
                    icon: 'ri-refresh-line',
                    text: 'Refresh Page',
                    shortcut: 'F5',
                    action: () => window.location.reload()
                });
            }
        }

        if (isLogout) {
            menuItems.push({
                icon: 'ri-logout-box-line',
                text: 'Sign Out',
                action: () => {
                    const logoutForm = document.getElementById('logoutform');
                    if (logoutForm) logoutForm.submit();
                },
                danger: true
            });
        }

        renderMenu(menuItems);
        positionMenu(e.clientX, e.clientY);
        showMenu();
    }

    function renderMenu(items) {
        let html = '';

        items.forEach(item => {
            if (item.type === 'header') {
                html += `<div class="context-menu-header">${escapeHtml(item.text)}</div>`;
            } else if (item.type === 'divider') {
                html += '<div class="context-menu-divider"></div>';
            } else {
                const classes = ['context-menu-item'];
                if (item.primary) classes.push('primary');
                if (item.danger) classes.push('danger');
                if (item.disabled) classes.push('disabled');
                if (item.hasSubmenu) classes.push('has-submenu');

                html += `
                    <div class="${classes.join(' ')}" data-action="${items.indexOf(item)}">
                        <i class="${item.icon}"></i>
                        <span>${escapeHtml(item.text)}</span>
                        ${item.shortcut ? `<span class="shortcut">${item.shortcut}</span>` : ''}
                    </div>
                `;
            }
        });

        menuElement.innerHTML = html;

        // Bind click events
        menuElement.querySelectorAll('.context-menu-item').forEach(el => {
            el.addEventListener('click', function() {
                const index = parseInt(this.dataset.action);
                const item = items[index];
                if (item && item.action && !item.disabled) {
                    hideMenu();
                    item.action();
                }
            });
        });
    }

    function positionMenu(x, y) {
        const menuRect = menuElement.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Temporarily show to get dimensions
        menuElement.style.visibility = 'hidden';
        menuElement.style.display = 'block';
        const menuWidth = menuElement.offsetWidth;
        const menuHeight = menuElement.offsetHeight;
        menuElement.style.visibility = '';
        menuElement.style.display = '';

        // Determine position and origin
        let left = x;
        let top = y;
        let originX = 'left';
        let originY = 'top';

        // Adjust if menu would go off-screen
        if (x + menuWidth > viewportWidth - 10) {
            left = x - menuWidth;
            originX = 'right';
        }

        if (y + menuHeight > viewportHeight - 10) {
            top = y - menuHeight;
            originY = 'bottom';
        }

        // Ensure menu stays on screen
        left = Math.max(10, left);
        top = Math.max(10, top);

        menuElement.style.left = left + 'px';
        menuElement.style.top = top + 'px';

        // Set transform origin for animation
        menuElement.className = 'context-menu';
        if (originX === 'right' || originY === 'bottom') {
            menuElement.classList.add(`origin-${originY}-${originX}`);
        }
    }

    function showMenu() {
        overlayElement.style.display = 'block';
        menuElement.style.display = 'block';

        // Trigger reflow for animation
        menuElement.offsetHeight;
        menuElement.classList.add('visible');
    }

    function hideMenu() {
        menuElement.classList.remove('visible');
        overlayElement.style.display = 'none';

        setTimeout(() => {
            menuElement.style.display = 'none';
        }, 150);

        currentTarget = null;
    }

    function copyToClipboard(text) {
        // Build full URL if relative
        const url = new URL(text, window.location.origin).href;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard');
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            showToast('Link copied to clipboard');
        } catch (err) {
            showToast('Failed to copy link', 'error');
        }

        document.body.removeChild(textarea);
    }

    function showToast(message, type = 'success') {
        // Check if toast container exists
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 10001;
                display: flex;
                flex-direction: column;
                gap: 8px;
            `;
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${type === 'success' ? '#107c10' : '#d13438'};
            color: white;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        `;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });

        // Remove after delay
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Expose for external use
    window.ContextMenu = {
        show: showMenu,
        hide: hideMenu,
        showToast: showToast
    };
})();
