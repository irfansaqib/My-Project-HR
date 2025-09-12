<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>DASHBOARD</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('business.edit', Auth::user()->business_id) }}" class="nav-link {{ request()->routeIs('business.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>BUSINESS PROFILE</p>
            </a>
        </li>

        <li class="nav-item has-treeview {{ request()->routeIs('users.*', 'roles.*', 'email-configuration.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>
                    ADMINISTRATION
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item has-treeview {{ request()->routeIs('users.*', 'roles.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Users and Roles <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                             <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('email-configuration.edit') }}" class="nav-link {{ request()->routeIs('email-configuration.*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Settings</p>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Future: CLIENT MANAGEMENT --}}

        <li class="nav-item has-treeview {{ request()->routeIs(['employees.*', 'designations.*', 'departments.*', 'attendances.*', 'reports.*', 'shifts.*', 'shift-assignments.*', 'holidays.*', 'leave-types.*', 'leave-requests.*', 'salary-components.*', 'salaries.*', 'tax-rates.*', 'payrolls.*']) ? 'menu-open' : '' }}">
            <a href="#" class="nav-link">
                <i class="nav-icon fas fa-users"></i>
                <p>
                    HR MANAGEMENT
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item has-treeview {{ request()->routeIs('employees.*', 'designations.*', 'departments.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Employees <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Employees List</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('designations.index') }}" class="nav-link {{ request()->routeIs('designations.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Designations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Departments</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview {{ request()->routeIs(['attendances.*', 'reports.attendance', 'reports.attendance-calendar', 'shifts.*', 'shift-assignments.*', 'holidays.*']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Attendance & Shifts <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                         <li class="nav-item">
                            <a href="{{ route('attendances.index') }}" class="nav-link {{ request()->routeIs('attendances.index') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Daily Attendance</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('attendances.bulk.create') }}" class="nav-link {{ request()->routeIs('attendances.bulk.create') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Bulk Mark Attendance</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('shifts.index') }}" class="nav-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Work Shifts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('shift-assignments.create') }}" class="nav-link {{ request()->routeIs('shift-assignments.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Assign Shifts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('holidays.index') }}" class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Public Holidays</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview {{ request()->routeIs('leave-types.*', 'leave-requests.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Leaves <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('leave-requests.create') }}" class="nav-link {{ request()->routeIs('leave-requests.create') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Apply For Leaves</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave-requests.index') }}" class="nav-link {{ request()->routeIs('leave-requests.index') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Leave Applications</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('leave-types.index') }}" class="nav-link {{ request()->routeIs('leave-types.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Leave Types</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview {{ request()->routeIs('salary-components.*', 'salaries.*', 'tax-rates.*', 'payrolls.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Payroll <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('salary-components.index') }}" class="nav-link {{ request()->routeIs('salary-components.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Salary Components</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('salaries.index') }}" class="nav-link {{ request()->routeIs('salaries.index') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Salary Sheets</p>
                            </a>
                        </li>
                        <li class="nav-item">
                             <a href="{{ route('tax-rates.index') }}" class="nav-link {{ request()->routeIs('tax-rates.*') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Tax Rates</p>
                            </a>
                        </li>
                         <li class="nav-item">
                            <a href="{{ route('payrolls.history') }}" class="nav-link {{ request()->routeIs('payrolls.history') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Payroll History</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview {{ request()->routeIs(['reports.attendance', 'reports.attendance-calendar']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Reports <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('reports.attendance') }}" class="nav-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Attendance Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.attendance-calendar') }}" class="nav-link {{ request()->routeIs('reports.attendance-calendar') ? 'active' : '' }}">
                                <i class="far fa-dot-circle nav-icon"></i>
                                <p>Attendance Calendar</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('client-credentials.index') }}" class="nav-link {{ request()->routeIs('client-credentials.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-key"></i>
                <p>CLIENT CREDENTIALS</p>
            </a>
        </li>
    </ul>
</nav>