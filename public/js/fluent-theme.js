/* ============================================
   Azure Fluent UI Theme - JavaScript
   ============================================ */

/* ============================================
   Page Loader & Progress Bar
   ============================================ */
// Hide loader when page is fully loaded
window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        // Small delay to ensure smooth transition
        setTimeout(function() {
            loader.classList.add('hidden');
            // Remove from DOM after animation
            setTimeout(function() {
                loader.style.display = 'none';
            }, 400);
        }, 200);
    }
});

// Progress bar functions
function showProgressBar() {
    const progressBar = document.getElementById('progressBar');
    if (progressBar) {
        progressBar.classList.add('active');
    }
}

function hideProgressBar() {
    const progressBar = document.getElementById('progressBar');
    if (progressBar) {
        progressBar.classList.remove('active');
    }
}

// Show loader on page navigation
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler to internal links for smooth transition
    document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript"]):not([target="_blank"])').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            // Only show loader for internal navigation
            if (href && !href.startsWith('http') && !href.startsWith('//') && !this.hasAttribute('data-no-loader')) {
                const loader = document.getElementById('pageLoader');
                if (loader) {
                    loader.style.display = 'flex';
                    loader.classList.remove('hidden');
                }
            }
        });
    });

    // Initialize all theme components
    initSidebar();
    initDropdowns();
    initAlerts();
    initNavigation();
    initAjaxLoading();
    initStickyHeader();
});

// AJAX loading indicator
function initAjaxLoading() {
    // Track active AJAX requests
    let activeRequests = 0;

    // jQuery AJAX hooks (if jQuery is available)
    if (typeof $ !== 'undefined') {
        $(document).ajaxStart(function() {
            activeRequests++;
            showProgressBar();
        });

        $(document).ajaxStop(function() {
            activeRequests--;
            if (activeRequests <= 0) {
                activeRequests = 0;
                hideProgressBar();
            }
        });

        $(document).ajaxError(function() {
            activeRequests = 0;
            hideProgressBar();
        });
    }

    // Native fetch interceptor
    const originalFetch = window.fetch;
    window.fetch = function() {
        activeRequests++;
        showProgressBar();

        return originalFetch.apply(this, arguments)
            .then(function(response) {
                activeRequests--;
                if (activeRequests <= 0) {
                    activeRequests = 0;
                    hideProgressBar();
                }
                return response;
            })
            .catch(function(error) {
                activeRequests--;
                if (activeRequests <= 0) {
                    activeRequests = 0;
                    hideProgressBar();
                }
                throw error;
            });
    };
}

/* ============================================
   Sticky Page Header
   ============================================ */
function initStickyHeader() {
    const pageHeader = document.querySelector('.fluent-page-header');
    const content = document.querySelector('.fluent-content');

    if (!pageHeader || !content) return;

    // Use IntersectionObserver for better performance
    const observer = new IntersectionObserver(
        ([entry]) => {
            // When the sentinel goes out of view (header is sticky)
            pageHeader.classList.toggle('is-sticky', !entry.isIntersecting);
        },
        {
            root: null,
            threshold: 0,
            rootMargin: '-1px 0px 0px 0px'
        }
    );

    // Create a sentinel element at the top of content
    const sentinel = document.createElement('div');
    sentinel.style.height = '1px';
    sentinel.style.width = '100%';
    sentinel.style.position = 'absolute';
    sentinel.style.top = '0';
    sentinel.style.left = '0';
    sentinel.style.pointerEvents = 'none';

    // Make content position relative for sentinel
    content.style.position = 'relative';
    content.insertBefore(sentinel, content.firstChild);

    observer.observe(sentinel);
}

/* ============================================
   Sidebar Toggle
   ============================================ */
function initSidebar() {
    const sidebar = document.querySelector('.fluent-sidebar');
    const toggleBtn = document.querySelector('.fluent-header-toggle');
    const overlay = document.querySelector('.fluent-sidebar-overlay');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                // Mobile: Toggle open/close
                sidebar.classList.toggle('open');
            } else {
                // Desktop: Toggle pinned/expanded state
                sidebar.classList.toggle('expanded');
                // Save state to localStorage
                localStorage.setItem('sidebarExpanded', sidebar.classList.contains('expanded'));

                // Update toggle button icon
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    if (sidebar.classList.contains('expanded')) {
                        icon.className = 'ri-menu-fold-line ri-lg';
                    } else {
                        icon.className = 'ri-menu-line ri-lg';
                    }
                }
            }

            // Trigger DataTable column adjustment
            setTimeout(function() {
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('.dataTable').each(function() {
                        if ($.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable().columns.adjust();
                        }
                    });
                }
            }, 300);
        });
    }

    // Close sidebar on overlay click (mobile)
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
        });
    }

    // Restore sidebar state (pinned expanded)
    if (localStorage.getItem('sidebarExpanded') === 'true' && window.innerWidth > 992) {
        sidebar?.classList.add('expanded');
        // Update toggle button icon
        const icon = toggleBtn?.querySelector('i');
        if (icon) {
            icon.className = 'ri-menu-fold-line ri-lg';
        }
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            sidebar?.classList.remove('open');
        }
    });
}

/* ============================================
   Navigation Submenus
   ============================================ */
function initNavigation() {
    const navItems = document.querySelectorAll('.fluent-nav-item.has-submenu');

    navItems.forEach(function(item) {
        const link = item.querySelector('.fluent-nav-link');

        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Close other open submenus (accordion behavior)
                navItems.forEach(function(otherItem) {
                    if (otherItem !== item && otherItem.classList.contains('open')) {
                        otherItem.classList.remove('open');
                    }
                });

                // Toggle current submenu
                item.classList.toggle('open');
            });
        }
    });

    // Auto-open submenu containing active link
    const activeLink = document.querySelector('.fluent-nav-submenu .fluent-nav-link.active');
    if (activeLink) {
        const parentItem = activeLink.closest('.fluent-nav-item.has-submenu');
        if (parentItem) {
            parentItem.classList.add('open');
        }
    }
}

/* ============================================
   Dropdown Menus
   ============================================ */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.fluent-dropdown');

    dropdowns.forEach(function(dropdown) {
        const trigger = dropdown.querySelector('.fluent-dropdown-trigger');

        if (trigger) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();

                // Close other dropdowns
                dropdowns.forEach(function(other) {
                    if (other !== dropdown) {
                        other.classList.remove('open');
                    }
                });

                // Toggle current dropdown
                dropdown.classList.toggle('open');
            });
        }
    });

    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        dropdowns.forEach(function(dropdown) {
            dropdown.classList.remove('open');
        });
    });

    // Prevent closing when clicking inside dropdown menu
    document.querySelectorAll('.fluent-dropdown-menu').forEach(function(menu) {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}

/* ============================================
   Alert Dismissal
   ============================================ */
function initAlerts() {
    const alerts = document.querySelectorAll('.fluent-alert[data-dismissible]');

    alerts.forEach(function(alert) {
        const closeBtn = alert.querySelector('.fluent-alert-close');

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.remove();
                }, 200);
            });
        }

        // Auto-dismiss after 5 seconds
        if (alert.dataset.autoDismiss) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.remove();
                }, 200);
            }, 5000);
        }
    });
}

/* ============================================
   Confirmation Dialogs
   ============================================ */
function confirmDelete(formId, message) {
    message = message || 'Are you sure you want to delete this item?';

    if (confirm(message)) {
        document.getElementById(formId).submit();
    }

    return false;
}

/* ============================================
   Toast Notifications
   ============================================ */
function showToast(message, type = 'info', duration = 3000) {
    const container = document.querySelector('.fluent-toast-container') || createToastContainer();

    const toast = document.createElement('div');
    toast.className = `fluent-toast fluent-toast-${type}`;
    toast.innerHTML = `
        <span class="fluent-toast-icon">
            ${getToastIcon(type)}
        </span>
        <span class="fluent-toast-message">${message}</span>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Remove after duration
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'fluent-toast-container';
    document.body.appendChild(container);
    return container;
}

function getToastIcon(type) {
    const icons = {
        success: '<i class="ri-check-line"></i>',
        error: '<i class="ri-close-line"></i>',
        warning: '<i class="ri-alert-line"></i>',
        info: '<i class="ri-information-line"></i>'
    };
    return icons[type] || icons.info;
}

/* ============================================
   Form Utilities
   ============================================ */

// Select All Checkbox
function initSelectAll() {
    const selectAllCheckbox = document.querySelector('.select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });

        rowCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    }
}

// Initialize Select All on page load
document.addEventListener('DOMContentLoaded', initSelectAll);

/* ============================================
   DataTable Theme Integration
   ============================================ */
if (typeof $.fn.DataTable !== 'undefined') {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                first: '<i class="ri-skip-back-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>',
                next: '<i class="ri-arrow-right-s-line"></i>',
                last: '<i class="ri-skip-forward-line"></i>'
            }
        },
        dom: '<"d-flex justify-between align-center mb-4"<"d-flex align-center gap-3"l<"dataTables_filter_wrapper"f>><"d-flex gap-2"B>>rt<"d-flex justify-between align-center mt-4"ip>',
    });
}

/* ============================================
   Loading State
   ============================================ */
function showLoading(element) {
    const loader = document.createElement('div');
    loader.className = 'fluent-loading-overlay';
    loader.innerHTML = '<div class="fluent-spinner"></div>';
    element.style.position = 'relative';
    element.appendChild(loader);
}

function hideLoading(element) {
    const loader = element.querySelector('.fluent-loading-overlay');
    if (loader) {
        loader.remove();
    }
}

/* ============================================
   Copy to Clipboard
   ============================================ */
function copyToClipboard(text, successMessage = 'Copied!') {
    navigator.clipboard.writeText(text).then(function() {
        showToast(successMessage, 'success');
    }).catch(function() {
        showToast('Failed to copy', 'error');
    });
}

/* ============================================
   Lightweight Link Prefetching
   Prefetches pages on hover for faster navigation
   ============================================ */
(function() {
    const prefetched = new Set();

    function prefetch(url) {
        if (prefetched.has(url)) return;
        prefetched.add(url);

        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }

    function isValidLink(link) {
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') ||
            href.startsWith('mailto:') || href.includes('logout') ||
            link.target === '_blank') return false;
        try {
            return new URL(href, location.origin).origin === location.origin;
        } catch { return false; }
    }

    // Prefetch on hover with small delay
    let hoverTimer;
    document.addEventListener('mouseover', function(e) {
        const link = e.target.closest('a[href]');
        if (link && isValidLink(link)) {
            hoverTimer = setTimeout(() => prefetch(link.href), 65);
        }
    }, { passive: true });

    document.addEventListener('mouseout', function(e) {
        if (e.target.closest('a[href]')) clearTimeout(hoverTimer);
    }, { passive: true });

    // Prefetch on touchstart for mobile
    document.addEventListener('touchstart', function(e) {
        const link = e.target.closest('a[href]');
        if (link && isValidLink(link)) prefetch(link.href);
    }, { passive: true });
})();
