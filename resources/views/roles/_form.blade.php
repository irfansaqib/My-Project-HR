@push('styles')
<style>
    .permission-tabs .nav-link { border-bottom: 3px solid transparent !important; color: #6c757d; cursor: pointer; }
    .permission-tabs .nav-link.active { font-weight: bold; border-color: #007bff !important; color: #007bff !important; }
    
    .permission-table { border: 1px solid #dee2e6; width: 100%; }
    .permission-table thead { background-color: #f4f6f9; color: #495057; }
    .permission-table td, .permission-table th { vertical-align: middle !important; padding: 8px; }
    
    .module-name { font-weight: 600; font-size: 0.95rem; }
    .module-name i { width: 25px; text-align: center; color: #17a2b8; margin-right: 8px; }

    .checkbox-cell { text-align: center; }
    .other-actions-cell { display: flex; flex-wrap: wrap; gap: 10px; }
    .other-actions-cell .custom-control { margin-right: 5px; }
</style>
@endpush

<div class="card-body">
    <div class="form-group">
        <label for="name">Role Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name ?? '') }}" required placeholder="e.g. HR Manager">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <hr>
    <h5 class="mt-4 mb-3"><i class="fas fa-shield-alt text-primary mr-2"></i>Assign Permissions</h5>

    <ul class="nav nav-tabs permission-tabs" id="permissionTabs" role="tablist">
        @foreach($permissions as $tabName => $modules)
            <li class="nav-item">
                <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ Str::slug($tabName) }}-tab" data-toggle="tab" href="#{{ Str::slug($tabName) }}" role="tab">
                    {{ $tabName }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content border-left border-right border-bottom p-3 bg-white" id="permissionTabsContent">
        @php
            // Define Icons for all modules
            $icons = [
                // Admin
                'user' => 'fas fa-users-cog', 'role' => 'fas fa-user-shield', 'business' => 'fas fa-building', 'email-configuration' => 'fas fa-envelope-open-text',
                // HR
                'employee' => 'fas fa-user-tie', 'department' => 'fas fa-sitemap', 'designation' => 'fas fa-id-card', 
                'shift' => 'fas fa-clock', 'shift-assignment' => 'fas fa-calendar-check', 'warning' => 'fas fa-exclamation-triangle', 
                'incentive' => 'fas fa-gift', 'employee-exit' => 'fas fa-sign-out-alt',
                // Payroll
                'salary-sheet' => 'fas fa-file-invoice-dollar', 'payroll' => 'fas fa-money-check-alt', 'salary-component' => 'fas fa-puzzle-piece', 'tax-rate' => 'fas fa-percent',
                'loan' => 'fas fa-hand-holding-usd', 'fund' => 'fas fa-piggy-bank', 'fund-transaction' => 'fas fa-exchange-alt', 'final-settlement' => 'fas fa-file-signature',
                // Leave
                'attendance' => 'fas fa-user-clock', 'leave-request' => 'fas fa-envelope', 'leave-type' => 'fas fa-calendar-plus', 
                'leave-encashment' => 'fas fa-coins', 'holiday' => 'fas fa-umbrella-beach',
                // Tasks
                'task' => 'fas fa-tasks', 'task-category' => 'fas fa-layer-group', 'recurring-task' => 'fas fa-sync',
                // Clients
                'client' => 'fas fa-briefcase', 'client-credential' => 'fas fa-key', 'tax-service' => 'fas fa-calculator'
            ];
        @endphp

        @foreach($permissions as $tabName => $modules)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ Str::slug($tabName) }}" role="tabpanel">
                <div class="table-responsive mt-2">
                    <table class="table table-hover permission-table">
                        <thead>
                            <tr class="text-center bg-light">
                                <th class="text-left" style="width: 25%;">Module</th>
                                <th style="width: 10%;">View/List</th>
                                <th style="width: 10%;">Create</th>
                                <th style="width: 10%;">Edit</th>
                                <th style="width: 10%;">Delete</th>
                                <th class="text-left">Special Actions</th>
                                <th style="width: 5%;">All</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $module => $permissionData)
                                <tr>
                                    <td class="module-name">
                                        <i class="{{ $icons[$module] ?? 'fas fa-circle' }}"></i>
                                        {{ ucwords(str_replace(['-', '_'], ' ', $module)) }}
                                    </td>
                                    
                                    @foreach(['list', 'create', 'edit', 'delete'] as $action)
                                        <td class="checkbox-cell">
                                            @php 
                                                // Map 'view' to 'list' for display if needed
                                                $p = $permissionData['standard'][$action] ?? ($action == 'list' ? ($permissionData['standard']['view'] ?? null) : null);
                                            @endphp

                                            @if($p)
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input permission-checkbox" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $p->name }}" 
                                                           id="perm_{{ $p->id }}" 
                                                           data-row="{{ $module }}" 
                                                           {{ (isset($role) && $role->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="perm_{{ $p->id }}"></label>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="other-actions-cell">
                                        @foreach($permissionData['other'] as $p)
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input permission-checkbox" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $p->name }}" 
                                                       id="perm_{{ $p->id }}" 
                                                       data-row="{{ $module }}"
                                                       {{ (isset($role) && $role->hasPermissionTo($p->name)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="perm_{{ $p->id }}" style="font-size: 0.85rem;">
                                                    {{ ucwords(str_replace(['-', '_'], ' ', Str::after($p->name, $module.'-'))) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </td>

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
<script>
    // Logic to Handle "Select All" for a specific row
    $('.select-all-row').change(function() {
        let rowId = $(this).data('row');
        let isChecked = $(this).is(':checked');
        $(`input[data-row="${rowId}"].permission-checkbox`).prop('checked', isChecked);
    });

    // If all checkboxes in a row are checked manually, check the "Select All" box
    $('.permission-checkbox').change(function() {
        let rowId = $(this).data('row');
        let allChecked = $(`input[data-row="${rowId}"].permission-checkbox`).length === $(`input[data-row="${rowId}"].permission-checkbox:checked`).length;
        $(`#select-all-${rowId}`).prop('checked', allChecked);
    });
</script>
@endpush