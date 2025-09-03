@push('styles')
<style>
    .permission-tabs .nav-link {
        border-bottom: 3px solid transparent !important;
        color: #6c757d;
    }
    .permission-tabs .nav-link.active {
        font-weight: bold;
    }
    /* !important is used to override default theme styles */
    #administration-tab.active {
        border-color: #007bff !important;
        color: #007bff !important;
    }
    #hr-payroll-tab.active {
        border-color: #28a745 !important;
        color: #28a745 !important;
    }
    #leave-management-tab.active {
        border-color: #ffc107 !important;
        color: #ffc107 !important;
    }

    .permission-table { border: 1px solid #dee2e6; }
    .permission-table thead { background-color: #f8f9fa; color: #495057; border-bottom: 2px solid #dee2e6; }
    .permission-table td, .permission-table th { border: 1px solid #dee2e6; vertical-align: middle !important; }
    .permission-table .module-name { font-weight: 600; }
    .permission-table .module-name i { margin-right: 8px; width: 20px; text-align: center; color: #007bff; }
    .permission-table .custom-control { min-height: auto; padding-left: 0; display: flex; justify-content: center; align-items: center; }
    .other-actions-cell { display: flex; flex-wrap: wrap; gap: 15px; align-items: center; }
</style>
@endpush

<div class="card-body">
    <div class="form-group">
        <label for="name">Role Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <hr>
    <h5 class="mt-3 mb-3">Assign Permissions</h5>

    <ul class="nav nav-tabs permission-tabs" id="permissionTabs" role="tablist">
        @foreach($permissions as $tabName => $modules)
            @if(!empty(array_filter($modules, fn($m) => $m->isNotEmpty())))
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ Str::slug($tabName) }}-tab" data-toggle="tab" href="#{{ Str::slug($tabName) }}" role="tab">{{ $tabName }}</a>
                </li>
            @endif
        @endforeach
    </ul>

    <div class="tab-content" id="permissionTabsContent">
        @foreach($permissions as $tabName => $modules)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ Str::slug($tabName) }}" role="tabpanel">
                <div class="table-responsive pt-3">
                    <table class="table table-bordered table-sm permission-table">
                        <thead>
                            <tr class="text-center">
                                <th class="text-left" style="width: 25%;">Module Name</th>
                                <th>View</th><th>Create</th><th>Edit</th><th>Delete</th>
                                <th class="text-left">Other Actions</th>
                                <th>All</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $actions = ['view', 'create', 'edit', 'delete'];
                                $icons = [
                                    'user' => 'fas fa-users-cog', 'role' => 'fas fa-user-shield', 'business' => 'fas fa-briefcase',
                                    'employee' => 'fas fa-user-tie', 'department' => 'fas fa-building', 'designation' => 'fas fa-id-badge',
                                    'salary-component' => 'fas fa-puzzle-piece', 'tax-rate' => 'fas fa-percent', 'salary-sheet' => 'fas fa-file-invoice-dollar',
                                    'payslip' => 'fas fa-receipt', 'payroll' => 'fas fa-cash-register', 'leave-type' => 'fas fa-calendar-plus',
                                    'leave-application' => 'fas fa-calendar-check', 'client-login-credential' => 'fas fa-key'
                                ];
                            @endphp
                            @foreach($modules as $module => $permissionGroup)
                                @if($permissionGroup->isNotEmpty())
                                <tr>
                                    <td class="module-name"><i class="{{ $icons[$module] ?? 'fas fa-cogs' }}"></i>{{ ucwords(str_replace(['-', '_'], ' ', $module)) }}</td>
                                    @foreach($actions as $action)
                                        <td class="text-center">
                                            @php $p = $permissionGroup->firstWhere('name', "$module-$action"); @endphp
                                            @if($p)
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $p->name }}" id="perm_{{ $p->id }}" data-row="{{ $module }}" {{ (isset($role) && $role->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $p->id }}"></label>
                                            </div>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="other-actions-cell">
                                        @foreach($permissionGroup as $p)
                                            @if(!in_array(Str::after($p->name, $module.'-'), $actions))
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $p->name }}" id="perm_{{ $p->id }}" data-row="{{ $module }}" {{ (isset($role) && $role->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $p->id }}">{{ ucwords(str_replace(['-', '_'], ' ', Str::after($p->name, $module.'-'))) }}</label>
                                            </div>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input select-all-row" id="select-all-{{ $module }}" data-row="{{ $module }}">
                                            <label class="custom-control-label" for="select-all-{{ $module }}"></label>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logic for "Select All" in a row
    document.querySelectorAll('.select-all-row').forEach(selectAll => {
        selectAll.addEventListener('click', function() {
            const module = this.getAttribute('data-row');
            document.querySelectorAll(`.permission-checkbox[data-row="${module}"]`).forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    });

    // Logic to check/uncheck the "Select All" checkbox based on individual checks
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('click', function() {
            const module = this.getAttribute('data-row');
            const allInRow = document.querySelectorAll(`.permission-checkbox[data-row="${module}"]`);
            const selectAllForRow = document.querySelector(`.select-all-row[data-row="${module}"]`);
            selectAllForRow.checked = Array.from(allInRow).every(cb => cb.checked);
        });
    });
});
</script>
@endpush