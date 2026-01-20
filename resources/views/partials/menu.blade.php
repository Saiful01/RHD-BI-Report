<aside class="fluent-sidebar" id="sidebar" oncontextmenu="return handleSidebarContext(event);">
    <!-- Brand -->
    <div class="fluent-sidebar-brand">
        <div class="fluent-sidebar-brand-logo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="12" width="4" height="9" rx="1" fill="#fff"/>
                <rect x="10" y="8" width="4" height="13" rx="1" fill="#fff"/>
                <rect x="17" y="3" width="4" height="18" rx="1" fill="#fff"/>
            </svg>
        </div>
        <span class="fluent-sidebar-brand-text">{{ trans('panel.site_title') }}</span>
    </div>

    <!-- Navigation -->
    <nav class="fluent-sidebar-nav">
        <!-- Main Navigation -->
        <div class="fluent-nav-section">
            <!-- Dashboard -->
            <div class="fluent-nav-item">
                <a href="{{ route('admin.home') }}" class="fluent-nav-link {{ request()->is('admin') ? 'active' : '' }}">
                    <span class="fluent-nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="7" height="7" rx="2" fill="#0078D4"/>
                            <rect x="14" y="3" width="7" height="7" rx="2" fill="#50E6FF"/>
                            <rect x="3" y="14" width="7" height="7" rx="2" fill="#50E6FF"/>
                            <rect x="14" y="14" width="7" height="7" rx="2" fill="#0078D4"/>
                        </svg>
                    </span>
                    <span class="fluent-nav-text">{{ trans('global.dashboard') }}</span>
                    <span class="fluent-nav-tooltip">{{ trans('global.dashboard') }}</span>
                </a>
            </div>
        </div>

        <!-- Data Management - TOP SECTION -->
        <div class="fluent-nav-section">
            <div class="fluent-nav-section-title">Data</div>

            @can('master_data_access')
                @can('station_access')
                    <div class="fluent-nav-item">
                        <a href="{{ route('admin.stations.index') }}" class="fluent-nav-link {{ request()->is('admin/stations*') ? 'active' : '' }}">
                            <span class="fluent-nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#D13438"/>
                                    <circle cx="12" cy="9" r="2.5" fill="#fff"/>
                                </svg>
                            </span>
                            <span class="fluent-nav-text">{{ trans('cruds.station.title') }}</span>
                            <span class="fluent-nav-tooltip">{{ trans('cruds.station.title') }}</span>
                        </a>
                    </div>
                @endcan
                @can('daily_weather_access')
                    <div class="fluent-nav-item">
                        <a href="{{ route('admin.daily-weathers.index') }}" class="fluent-nav-link {{ request()->is('admin/daily-weathers*') ? 'active' : '' }}">
                            <span class="fluent-nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="6" r="4" fill="#FFB900"/>
                                    <path d="M12 1v2M12 10v2M17 6h2M3 6h2M15.5 2.5l1 1M6.5 2.5l-1 1M15.5 9.5l1-1M6.5 9.5l-1-1" stroke="#FFB900" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M4 16h16c0 0 0-4-4-4h-8c-4 0-4 4-4 4z" fill="#50E6FF"/>
                                    <ellipse cx="12" cy="18" rx="8" ry="3" fill="#50E6FF"/>
                                </svg>
                            </span>
                            <span class="fluent-nav-text">{{ trans('cruds.dailyWeather.title') }}</span>
                            <span class="fluent-nav-tooltip">{{ trans('cruds.dailyWeather.title') }}</span>
                        </a>
                    </div>
                @endcan
            @endcan

            @can('tender_data_access')
                @can('tender_access')
                    <div class="fluent-nav-item">
                        <a href="{{ route('admin.tender.index') }}" class="fluent-nav-link {{ request()->is('admin/tender') && !request()->is('admin/tender-item*') ? 'active' : '' }}">
                            <span class="fluent-nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <rect x="4" y="2" width="16" height="20" rx="2" fill="#0078D4"/>
                                    <path d="M8 7h8M8 11h8M8 15h5" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="fluent-nav-text">Tenders</span>
                            <span class="fluent-nav-tooltip">Tenders</span>
                        </a>
                    </div>
                @endcan
            @endcan
        </div>

        <!-- Reports - SECOND SECTION -->
        <div class="fluent-nav-section">
            <div class="fluent-nav-section-title">Reports</div>

            @can('daily_weather_access')
                <div class="fluent-nav-item">
                    <a href="{{ route('admin.daily-weathers.index') }}?view=pavementAnalysis" class="fluent-nav-link {{ request()->is('admin/daily-weathers*') && request()->get('view') == 'pavementAnalysis' ? 'active' : '' }}">
                        <span class="fluent-nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M4 19h16" stroke="#107C10" stroke-width="3" stroke-linecap="round"/>
                                <path d="M4 15h16" stroke="#107C10" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                                <path d="M6 11h12" stroke="#107C10" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                                <path d="M12 3v5M9 5l3-2 3 2" stroke="#FFB900" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="fluent-nav-text">Pavement Analysis</span>
                        <span class="fluent-nav-tooltip">Pavement Temperature Analysis</span>
                    </a>
                </div>
                <div class="fluent-nav-item">
                    <a href="{{ route('admin.construction-weather.index') }}" class="fluent-nav-link {{ request()->is('admin/construction-weather*') ? 'active' : '' }}">
                        <span class="fluent-nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="10" width="18" height="11" rx="1" stroke="#8B5CF6" stroke-width="1.5" fill="none"/>
                                <path d="M7 10V7a5 5 0 0 1 10 0v3" stroke="#8B5CF6" stroke-width="1.5" fill="none"/>
                                <circle cx="6" cy="4" r="2" fill="#F59E0B"/>
                                <path d="M6 6v2" stroke="#F59E0B" stroke-width="1"/>
                                <path d="M18 5l-2 2M18 5l2 2" stroke="#3B82F6" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="fluent-nav-text">Construction Weather</span>
                        <span class="fluent-nav-tooltip">Construction Weather Analysis</span>
                    </a>
                </div>
            @endcan

            @can('tender_data_access')
                @can('item_summary_access')
                    <div class="fluent-nav-item">
                        <a href="{{ route('admin.tender-item.summeryReport') }}" class="fluent-nav-link {{ request()->is('admin/tender-item-summary*') ? 'active' : '' }}">
                            <span class="fluent-nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2a10 10 0 1 0 10 10" stroke="#E3008C" stroke-width="2" fill="none"/>
                                    <path d="M12 2v10h10a10 10 0 0 0-10-10z" fill="#E3008C"/>
                                    <path d="M12 12l7-7" stroke="#fff" stroke-width="1"/>
                                </svg>
                            </span>
                            <span class="fluent-nav-text">Item Summary</span>
                            <span class="fluent-nav-tooltip">Item Summary</span>
                        </a>
                    </div>
                @endcan
            @endcan
        </div>

        <!-- User Management - BOTTOM SECTION -->
        @can('user_management_access')
            <div class="fluent-nav-section">
                <div class="fluent-nav-section-title">Settings</div>

                <div class="fluent-nav-item">
                    <a href="{{ route('admin.users.index') }}" class="fluent-nav-link {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}">
                        <span class="fluent-nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <circle cx="9" cy="7" r="3" fill="#6B4FBB"/>
                                <path d="M2 19c0-3 3-5 7-5s7 2 7 5" stroke="#6B4FBB" stroke-width="2" stroke-linecap="round" fill="none"/>
                                <circle cx="17" cy="10" r="2" fill="#8764B8"/>
                                <path d="M14 19c0-2 1.5-3 3-3s3 1 3 3" stroke="#8764B8" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                            </svg>
                        </span>
                        <span class="fluent-nav-text">User Management</span>
                        <span class="fluent-nav-tooltip">User Management</span>
                    </a>
                </div>
            </div>
        @endcan

        <!-- Account Section - at very bottom -->
        <div class="fluent-nav-section fluent-nav-section-bottom">
            <div class="fluent-nav-section-title">Account</div>
            @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
                @can('profile_password_edit')
                    <div class="fluent-nav-item">
                        <a href="{{ route('profile.password.edit') }}" class="fluent-nav-link {{ request()->is('profile/password*') ? 'active' : '' }}">
                            <span class="fluent-nav-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="3" fill="#605E5C"/>
                                    <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12" stroke="#605E5C" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="fluent-nav-text">{{ trans('global.change_password') }}</span>
                            <span class="fluent-nav-tooltip">{{ trans('global.change_password') }}</span>
                        </a>
                    </div>
                @endcan
            @endif
            <div class="fluent-nav-item">
                <a href="#" class="fluent-nav-link fluent-nav-link-logout" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <span class="fluent-nav-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="#D13438" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 17l5-5-5-5M21 12H9" stroke="#D13438" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="fluent-nav-text">{{ trans('global.logout') }}</span>
                    <span class="fluent-nav-tooltip">{{ trans('global.logout') }}</span>
                </a>
            </div>
        </div>
    </nav>
</aside>
