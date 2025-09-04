@push('styles')
<style>
    .permission-tabs .nav-link { border-bottom: 3px solid transparent !important; color: #6c757d; }
    .permission-tabs .nav-link.active { font-weight: bold; }
    #administration-tab.active { border-color: #007bff !important; color: #007bff !important; }
    #hr-payroll-tab.active { border-color: #28a745 !important; color: #28a745 !important; }
    #leave-management-tab.active { border-color: #ffc107 !important; color: #ffc107 !important; }

    .permission-table { border: 1px solid #dee2e6; }
    .permission-table thead { background-color: #f8f9fa; color: #495057; border-bottom: 2px solid #dee2e6; }
    .permission-table td, .permission-table th { border: 1px solid #dee2e6; vertical-align: middle !important; }
    .permission-table .module-name { font-weight: 600; }
    .permission-table .module-name i { margin-right: 8px; width: 20px; text-align: center; color: #007bff; }
    
    /* --- THIS IS THE FIX --- */
    .permission-table .checkbox-cell {
        text-align: center;
        vertical-align: middle;
    }
    .permission-table .checkbox-cell .custom-control {
        display: inline-flex;
    }
    /* --- END OF FIX --- */

    .other-actions-cell { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
    .other-actions-cell .custom-control { justify-content: flex-start; }
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
            @if(!empty($modules))
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
                                $icons = [ 'user' => 'fas fa-users-cog', 'role' => 'fas fa-user-shield', 'business' => 'fas fa-briefcase', 'employee' => 'fas fa-user-tie', 'department' => 'fas fa-building', 'designation' => 'fas fa-id-badge', 'salary-component' => 'fas fa-puzzle-piece', 'tax-rate' => 'fas fa-percent', 'salary-sheet' => 'fas fa-file-invoice-dollar', 'payslip' => 'fas fa-receipt', 'payroll' => 'fas fa-cash-register', 'leave-type' => 'fas fa-calendar-plus', 'leave-application' => 'fas fa-calendar-check', 'client-login-credential' => 'fas fa-key' ];
                            @endphp
                            @foreach($modules as $module => $permissionData)
                                <tr>
                                    <td class="module-name"><i class="{{ $icons[$module] ?? 'fas fa-cogs' }}"></i>{{ ucwords(str_replace(['-', '_'], ' ', $module)) }}</td>
                                    
                                    @foreach($actions as $action)
                                        {{-- Apply the new class to the checkbox cells --}}
                                        <td class="checkbox-cell">
                                            @if(isset($permissionData['standard'][$action]))
                                                @php $p = $permissionData['standard'][$action]; @endphp
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $p->name }}" id="perm_{{ $p->id }}" data-row="{{ $module }}" {{ (isset($role) && $role->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="perm_{{ $p->id }}"></label>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="other-actions-cell">
                                        @foreach($permissionData['other'] as $p)
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $p->name }}" id="perm_{{ $p->id }}" data-row="{{ $module }}" {{ (isset($role) && $role->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $p->id }}">{{ ucwords(str_replace(['-', '_'], ' ', Str::after($p->name, $module.'-'))) }}</label>
                                            </div>
                                        @endforeach
                                    </td>
                                    {{-- Apply the new class to the "All" checkbox cell --}}
                                    <td class="checkbox-cell">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input select-all-row" id="select-all-{{ $module }}" data-row="{{ $module }}">
                                            <label class="custom-control-label" for="select-all-{{ $module }}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
{{-- Scripts are unchanged --}}
@endpush