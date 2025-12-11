<div class="row">
    {{-- CLIENT SELECTION --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Select Client <span class="text-danger">*</span></label>
            <select name="client_id" class="form-control select2" required>
                <option value="">-- Choose Client --</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ (old('client_id', $task->client_id ?? '') == $c->id) ? 'selected' : '' }}>
                        {{ $c->business_name }} ({{ $c->contact_person }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ASSIGN EMPLOYEE --}}
    <div class="col-md-6">
        <div class="form-group">
            <label>Assign To <span class="text-danger">*</span></label>
            <select name="assigned_to" class="form-control select2" required>
                <option value="">-- Choose Employee --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ (old('assigned_to', $task->assigned_to ?? '') == $emp->id) ? 'selected' : '' }}>
                        {{ $emp->name }} ({{ $emp->designation->title ?? '' }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<hr>
<h6 class="text-primary font-weight-bold">Task Categorization</h6>

{{-- 3-LEVEL CATEGORY DROPDOWNS --}}
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label class="small text-muted">Category (Main)</label>
            <select id="cat_lvl_0" class="form-control" onchange="loadLevel1()">
                <option value="">-- Select --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (isset($selectedLvl0) && $selectedLvl0 == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="small text-muted">Sub-Category Level 1</label>
            <select id="cat_lvl_1" class="form-control" disabled onchange="loadLevel2()">
                <option value="">-- Select Main First --</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="small text-muted">Sub-Category Level 2</label>
            <select name="category_id" id="cat_lvl_2" class="form-control" disabled required>
                <option value="">-- Select Level 1 First --</option>
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <label>Task Description <span class="text-danger">*</span></label>
    <textarea name="description" class="form-control" rows="3" required>{{ old('description', $task->description ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Start Date & Time</label>
            <input type="datetime-local" name="start_date" class="form-control" required 
                   value="{{ old('start_date', isset($task->start_date) ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d\TH:i') : '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>End Date & Time</label>
            <input type="datetime-local" name="due_date" class="form-control" 
                   value="{{ old('due_date', isset($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d\TH:i') : '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Priority</label>
            <select name="priority" class="form-control">
                @foreach(['Normal', 'Urgent', 'Very Urgent'] as $p)
                    <option value="{{ $p }}" {{ (old('priority', $task->priority ?? '') == $p) ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

@if(isset($task))
<div class="form-group">
    <label>Status</label>
    <select name="status" class="form-control">
        @foreach(['Pending', 'In Progress', 'Completed', 'Closed'] as $s)
            <option value="{{ $s }}" {{ $task->status == $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
</div>
@endif

<div class="text-right mt-3">
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i> Save Task</button>
</div>

<script>
    const allCats = @json($categories);
    const selLvl1 = "{{ $selectedLvl1 ?? '' }}";
    const selLvl2 = "{{ $selectedLvl2 ?? '' }}";

    function loadLevel1() {
        let pid = document.getElementById('cat_lvl_0').value;
        let l1 = document.getElementById('cat_lvl_1');
        let l2 = document.getElementById('cat_lvl_2');
        
        l1.innerHTML = '<option value="">-- Select --</option>';
        l2.innerHTML = '<option value="">-- Select --</option>';
        l1.disabled = true; l2.disabled = true;

        if(!pid) return;
        let p = allCats.find(c => c.id == pid);
        if(p && p.children) {
            p.children.forEach(c => {
                let s = (c.id == selLvl1) ? 'selected' : '';
                l1.innerHTML += `<option value="${c.id}" ${s}>${c.name}</option>`;
            });
            l1.disabled = false;
            if(selLvl1) loadLevel2();
        }
    }

    function loadLevel2() {
        let mid = document.getElementById('cat_lvl_0').value;
        let pid = document.getElementById('cat_lvl_1').value;
        let l2 = document.getElementById('cat_lvl_2');
        
        l2.innerHTML = '<option value="">-- Select --</option>';
        l2.disabled = true;

        if(!pid) return;
        let m = allCats.find(c => c.id == mid);
        let s1 = m.children.find(c => c.id == pid);
        
        if(s1 && s1.children) {
            s1.children.forEach(c => {
                let s = (c.id == selLvl2) ? 'selected' : '';
                l2.innerHTML += `<option value="${c.id}" ${s}>${c.name}</option>`;
            });
            l2.disabled = false;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        if(document.getElementById('cat_lvl_0').value) loadLevel1();
    });
</script>