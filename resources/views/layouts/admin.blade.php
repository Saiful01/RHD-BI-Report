<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', trans('panel.site_title'))</title>

    <!-- Preconnect to CDNs for faster loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://stackpath.bootstrapcdn.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Remix Icons (Modern Icon Set) -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Core Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/1.6.5/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-select/1.3.3/select.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css" rel="stylesheet" />

    <!-- Fluent UI Theme -->
    <link href="{{ asset('css/fluent-theme.css') }}" rel="stylesheet" />
    <!-- Fluent Components (Modular) -->
    <link href="{{ asset('css/fluent-components/loader.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components/layout.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components/components.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components/forms.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components/tables.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components/modals.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-components/utilities.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fluent-utilities.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/context-menu.css') }}" rel="stylesheet" />

    @yield('styles')
</head>

<body>
    <script>
    // Context menu handler - must be defined before sidebar loads
    function handleSidebarContext(e) {
        e.preventDefault();
        e.stopPropagation();

        var navLink = e.target.closest('.fluent-nav-link');
        if (navLink) {
            showContextMenu(e, navLink);
        }
        return false;
    }

    function showContextMenu(e, link) {
        // Remove existing menu
        var old = document.getElementById('ctxMenu');
        if (old) old.remove();

        var href = link.getAttribute('href');
        var text = link.querySelector('.fluent-nav-text');
        text = text ? text.textContent.trim() : 'Link';
        var isLogout = link.classList.contains('fluent-nav-link-logout');
        var isActive = link.classList.contains('active');

        var menu = document.createElement('div');
        menu.id = 'ctxMenu';
        menu.style.cssText = 'position:fixed;z-index:99999;min-width:180px;background:#fff;border:1px solid #e1e1e1;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.15);padding:6px 0;';

        var html = '<div style="padding:8px 14px 6px;font-size:11px;font-weight:600;color:#605e5c;border-bottom:1px solid #f3f2f1;margin-bottom:4px;">' + text + '</div>';

        if (!isLogout && href && href !== '#') {
            html += '<div class="ctx-item" data-action="open" style="display:flex;align-items:center;gap:10px;padding:8px 14px;font-size:13px;cursor:pointer;color:#0078d4;"><i class="ri-arrow-right-line"></i><span>Open</span></div>';
            html += '<div class="ctx-item" data-action="newtab" style="display:flex;align-items:center;gap:10px;padding:8px 14px;font-size:13px;cursor:pointer;"><i class="ri-external-link-line"></i><span>Open in New Tab</span></div>';
            html += '<div class="ctx-item" data-action="copy" style="display:flex;align-items:center;gap:10px;padding:8px 14px;font-size:13px;cursor:pointer;"><i class="ri-link"></i><span>Copy Link</span></div>';
            if (isActive) {
                html += '<div style="height:1px;background:#f3f2f1;margin:6px 0;"></div>';
                html += '<div class="ctx-item" data-action="refresh" style="display:flex;align-items:center;gap:10px;padding:8px 14px;font-size:13px;cursor:pointer;"><i class="ri-refresh-line"></i><span>Refresh</span></div>';
            }
        }
        if (isLogout) {
            html += '<div class="ctx-item" data-action="logout" style="display:flex;align-items:center;gap:10px;padding:8px 14px;font-size:13px;cursor:pointer;color:#d13438;"><i class="ri-logout-box-line"></i><span>Sign Out</span></div>';
        }

        menu.innerHTML = html;

        // Position
        var x = e.clientX, y = e.clientY;
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
        document.body.appendChild(menu);

        // Adjust if off screen
        var rect = menu.getBoundingClientRect();
        if (rect.right > window.innerWidth) menu.style.left = (x - rect.width) + 'px';
        if (rect.bottom > window.innerHeight) menu.style.top = (y - rect.height) + 'px';

        // Hover effect
        menu.querySelectorAll('.ctx-item').forEach(function(item) {
            item.onmouseenter = function() { this.style.background = '#f3f2f1'; };
            item.onmouseleave = function() { this.style.background = 'transparent'; };
            item.onclick = function() {
                var action = this.dataset.action;
                menu.remove();
                if (action === 'open') window.location.href = href;
                else if (action === 'newtab') window.open(href, '_blank');
                else if (action === 'copy') {
                    navigator.clipboard.writeText(new URL(href, location.origin).href);
                }
                else if (action === 'refresh') location.reload();
                else if (action === 'logout') document.getElementById('logoutform').submit();
            };
        });

        // Close on click outside
        setTimeout(function() {
            document.addEventListener('click', function closeCtx(ev) {
                if (!menu.contains(ev.target)) {
                    menu.remove();
                    document.removeEventListener('click', closeCtx);
                }
            });
        }, 10);
    }
    </script>

    <!-- Top Progress Bar -->
    <div class="fluent-progress-bar" id="progressBar">
        <div class="fluent-progress-bar-inner"></div>
    </div>

    <!-- Page Loader -->
    <div class="fluent-page-loader" id="pageLoader">
        <div class="fluent-loader-content">
            <div class="fluent-loader-spinner">
                <div class="fluent-loader-ring"></div>
                <div class="fluent-loader-ring"></div>
                <div class="fluent-loader-ring"></div>
                <svg class="fluent-loader-logo" width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="12" width="4" height="9" rx="1" fill="#0078D4"/>
                    <rect x="10" y="8" width="4" height="13" rx="1" fill="#00BCF2"/>
                    <rect x="17" y="3" width="4" height="18" rx="1" fill="#0078D4"/>
                </svg>
            </div>
            <div class="fluent-loader-text">Loading...</div>
        </div>
    </div>

    <div class="fluent-app">
        <!-- Sidebar -->
        @include('partials.menu')

        <!-- Sidebar Overlay (Mobile) -->
        <div class="fluent-sidebar-overlay"></div>

        <!-- Main Content -->
        <div class="fluent-main">
            <!-- Header -->
            <header class="fluent-header">
                <div class="fluent-header-left">
                    <button type="button" class="fluent-header-toggle" aria-label="Toggle sidebar">
                        <i class="ri-menu-line ri-lg"></i>
                    </button>
                    <h1 class="fluent-header-title d-md-none">{{ trans('panel.site_title') }}</h1>
                </div>

                <div class="fluent-header-right">
                    @if(count(config('panel.available_languages', [])) > 1)
                        <div class="fluent-dropdown">
                            <button class="fluent-btn fluent-btn-ghost fluent-dropdown-trigger">
                                <i class="ri-global-line"></i>
                                <span>{{ strtoupper(app()->getLocale()) }}</span>
                                <i class="ri-arrow-down-s-line"></i>
                            </button>
                            <div class="fluent-dropdown-menu">
                                @foreach(config('panel.available_languages') as $langLocale => $langName)
                                    <a class="fluent-dropdown-item" href="{{ url()->current() }}?change_language={{ $langLocale }}">
                                        <span>{{ strtoupper($langLocale) }}</span>
                                        <span class="text-secondary">{{ $langName }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- User Menu -->
                    <div class="fluent-dropdown fluent-user-menu">
                        <button class="fluent-user-button fluent-dropdown-trigger">
                            <div class="fluent-user-avatar">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="fluent-user-name d-md-none d-lg-inline">{{ auth()->user()->name ?? 'User' }}</span>
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                        <div class="fluent-dropdown-menu">
                            <a class="fluent-dropdown-item" href="{{ route('profile.password.edit') }}">
                                <i class="ri-lock-line"></i>
                                <span>{{ trans('global.change_password') }}</span>
                            </a>
                            <div class="fluent-dropdown-divider"></div>
                            <a class="fluent-dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                                <i class="ri-logout-box-line"></i>
                                <span>{{ trans('global.logout') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="fluent-content">
                @if(session('message'))
                    <div class="fluent-alert fluent-alert-success" data-dismissible data-auto-dismiss>
                        <span class="fluent-alert-icon">
                            <i class="ri-check-line ri-lg"></i>
                        </span>
                        <div class="fluent-alert-content">
                            {{ session('message') }}
                        </div>
                        <button type="button" class="fluent-btn fluent-btn-icon fluent-btn-ghost fluent-alert-close">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                @endif

                @if($errors->count() > 0)
                    <div class="fluent-alert fluent-alert-error">
                        <span class="fluent-alert-icon">
                            <i class="ri-error-warning-line ri-lg"></i>
                        </span>
                        <div class="fluent-alert-content">
                            <ul class="list-none m-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="fluent-footer">
                <span>Made with</span>
                <svg class="heart-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <span>by SR. All rights reserved.</span>
            </footer>
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logoutform" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Core Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/1.6.5/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/1.6.5/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.5.0/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-select/1.3.3/dataTables.select.min.js"></script>

    <!-- Form Libraries -->
    <script src="https://cdn.ckeditor.com/ckeditor5/16.0.0/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>

    <!-- Theme Scripts -->
    <script src="{{ asset('js/fluent-theme.js') }}"></script>
    <script src="{{ asset('js/context-menu.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Backup: Ensure sidebar context menu is disabled -->
    <script>
    (function(){
        var sidebar = document.getElementById('sidebar');
        if(sidebar) {
            sidebar.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            }, true);
        }
        document.addEventListener('DOMContentLoaded', function() {
            var sb = document.getElementById('sidebar') || document.querySelector('.fluent-sidebar');
            if(sb) {
                sb.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    return false;
                }, true);
            }
        });
    })();
    </script>

    <script>
        $(function() {
            let copyButtonTrans = '{{ trans('global.datatables.copy') }}'
            let csvButtonTrans = '{{ trans('global.datatables.csv') }}'
            let excelButtonTrans = '{{ trans('global.datatables.excel') }}'
            let pdfButtonTrans = '{{ trans('global.datatables.pdf') }}'
            let printButtonTrans = '{{ trans('global.datatables.print') }}'
            let colvisButtonTrans = '{{ trans('global.datatables.colvis') }}'
            let selectAllButtonTrans = '{{ trans('global.select_all') }}'
            let selectNoneButtonTrans = '{{ trans('global.deselect_all') }}'

            let languages = {
                'en': 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/English.json'
            };

            $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, { className: 'fluent-btn fluent-btn-sm' })
            $.extend(true, $.fn.dataTable.defaults, {
                language: {
                    url: languages['{{ app()->getLocale() }}']
                },
                columnDefs: [{
                    orderable: false,
                    className: 'select-checkbox',
                    targets: 0
                }, {
                    orderable: false,
                    searchable: false,
                    targets: -1
                }],
                select: {
                    style: 'multi+shift',
                    selector: 'td:first-child'
                },
                order: [],
                scrollX: true,
                pageLength: 100,
                dom: '<"d-flex flex-wrap justify-between align-center gap-3 mb-4"<"d-flex align-center gap-2"l><"d-flex flex-wrap gap-2"B><"flex-grow-1"f>>rt<"d-flex flex-wrap justify-between align-center gap-3 mt-4"ip>',
                buttons: [
                    {
                        extend: 'selectAll',
                        className: 'fluent-btn-primary',
                        text: '<i class="ri-checkbox-multiple-line mr-1"></i>' + selectAllButtonTrans,
                        exportOptions: { columns: ':visible' },
                        action: function(e, dt) {
                            e.preventDefault()
                            dt.rows().deselect();
                            dt.rows({ search: 'applied' }).select();
                        }
                    },
                    {
                        extend: 'selectNone',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-checkbox-blank-line mr-1"></i>' + selectNoneButtonTrans,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'copy',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-file-copy-line mr-1"></i>' + copyButtonTrans,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'csv',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-file-text-line mr-1"></i>' + csvButtonTrans,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'excel',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-file-excel-line mr-1"></i>' + excelButtonTrans,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'pdf',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-file-pdf-line mr-1"></i>' + pdfButtonTrans,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'print',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-printer-line mr-1"></i>' + printButtonTrans,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'colvis',
                        className: 'fluent-btn-secondary',
                        text: '<i class="ri-eye-line mr-1"></i>' + colvisButtonTrans,
                        exportOptions: { columns: ':visible' }
                    }
                ]
            });

            $.fn.dataTable.ext.classes.sPageButton = '';
        });
    </script>

    @yield('scripts')
</body>

</html>
