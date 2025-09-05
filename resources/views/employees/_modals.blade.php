{{-- Modal for adding a new Designation --}}
<div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDesignationModalLabel">Add New Designation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new_designation_name">Designation Name</label>
                    <input type="text" class="form-control" id="new_designation_name">
                    <div id="designation-error" class="text-danger mt-2 d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDesignationBtn">Save Designation</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal for adding a new Department --}}
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new_department_name">Department Name</label>
                    <input type="text" class="form-control" id="new_department_name">
                    <div id="department-error" class="text-danger mt-2 d-none"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDepartmentBtn">Save Department</button>
            </div>
        </div>
    </div>
</div>