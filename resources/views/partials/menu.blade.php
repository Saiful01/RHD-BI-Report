<div id="sidebar" class="c-sidebar c-sidebar-fixed c-sidebar-lg-show">

    <div class="c-sidebar-brand d-md-down-none">
        <a class="c-sidebar-brand-full h4" href="#">
            {{ trans('panel.site_title') }}
        </a>
    </div>

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.home") }}" class="c-sidebar-nav-link">
                <i class="c-sidebar-nav-icon fas fa-fw fa-tachometer-alt">

                </i>
                {{ trans('global.dashboard') }}
            </a>
        </li>
        @can('user_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/permissions*") ? "c-show" : "" }} {{ request()->is("admin/roles*") ? "c-show" : "" }} {{ request()->is("admin/users*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-users c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.userManagement.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('permission_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.permissions.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/permissions") || request()->is("admin/permissions/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-unlock-alt c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.permission.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('role_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.roles.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/roles") || request()->is("admin/roles/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-briefcase c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.role.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('user_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.users.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/users") || request()->is("admin/users/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-user c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.user.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('master_data_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/stations*") ? "c-show" : "" }} {{ request()->is("admin/daily-weathers*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-cogs c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.masterData.title') }}
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('station_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.stations.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/stations") || request()->is("admin/stations/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-location-arrow c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.station.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('daily_weather_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.daily-weathers.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/daily-weathers") || request()->is("admin/daily-weathers/*") ? "c-active" : "" }}">
                                <i class="fa-fw fab fa-weibo c-sidebar-nav-icon">

                                </i>
                                {{ trans('cruds.dailyWeather.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan


        @can('tender_data_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is("admin/tenders*") ? "c-show" : "" }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-file-contract c-sidebar-nav-icon"></i> Tender Info
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('tender_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.tender.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/tenders") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-list c-sidebar-nav-icon"></i>
                                Tender List
                            </a>
                        </li>
                    @endcan

                    @can('tender_item_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.tender-item.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/tenders/item") || request()->is("admin/tenders/item/*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-boxes c-sidebar-nav-icon"></i>
                                Tender Item List
                            </a>
                        </li>
                    @endcan

                    @can('item_summary_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route("admin.tender-item-summary.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/tenders/item-summary*") ? "c-active" : "" }}">
                                <i class="fa-fw fas fa-chart-pie c-sidebar-nav-icon"></i>
                                Item Summary
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan
        @can('weather_report_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.weather-reports.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/weather-reports") || request()->is("admin/weather-reports/*") ? "c-active" : "" }}">
                    <i class="fa-fw fas fa-flag c-sidebar-nav-icon">

                    </i>
                    {{ trans('cruds.weatherReport.title') }}
                </a>
            </li>
        @endcan
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link {{ request()->is('profile/password') || request()->is('profile/password/*') ? 'c-active' : '' }}" href="{{ route('profile.password.edit') }}">
                        <i class="fa-fw fas fa-key c-sidebar-nav-icon">
                        </i>
                        {{ trans('global.change_password') }}
                    </a>
                </li>
            @endcan
        @endif
        <li class="c-sidebar-nav-item">
            <a href="#" class="c-sidebar-nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                <i class="c-sidebar-nav-icon fas fa-fw fa-sign-out-alt">

                </i>
                {{ trans('global.logout') }}
            </a>
        </li>
    </ul>

</div>
