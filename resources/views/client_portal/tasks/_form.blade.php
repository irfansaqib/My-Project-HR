<h5 class="fw-bold text-primary mb-3">Service Details</h5>

{{-- DYNAMIC CATEGORY DROPDOWNS --}}
<div class="row mb-3">
    {{-- Level 0: Main Service --}}
    <div class="col-md-4">
        <label class="form-label small fw-bold text-muted">Main Service</label>
        <select id="cat_lvl_0" class="form-select" onchange="loadLevel1()">
            <option value="">-- Select --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" 
                    {{ (isset($selectedLvl0) && $selectedLvl0 == $cat->id) ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Level 1: Category --}}
    <div class="col-md-4">
        <label class="form-label small fw-bold text-muted">Category</label>
        <select id="cat_lvl_1" class="form-select" {{ isset($task) ? '' : 'disabled' }} onchange="loadLevel2()">
            <option value="">-- Select Main First --</option>
        </select>
    </div>

    {{-- Level 2: Sub-Category (Actual Input) --}}
    <div class="col-md-4">
        <label class="form-label small fw-bold text-muted">Sub-Category</label>
        <select name="category_id" id="cat_lvl_2" class="form-select" {{ isset($task) ? '' : 'disabled' }} required>
            <option value="">-- Select Category First --</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Description / Instructions</label>
    <textarea name="description" class="form-control" rows="4" required placeholder="Please describe the task...">{{ old('description', $task->description ?? '') }}</textarea>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Priority</label>
        <select name="priority" class="form-select">
            @foreach(['Normal', 'Urgent', 'Very Urgent'] as $p)
                <option value="{{ $p }}" {{ (old('priority', $task->priority ?? '') == $p) ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Preferred Due Date (Optional)</label>
        <input type="date" name="due_date" class="form-control" 
               value="{{ old('due_date', isset($task->due_date) ? $task->due_date->format('Y-m-d') : '') }}" 
               min="{{ date('Y-m-d') }}">
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-bold">Attachments</label>
    <input type="file" name="attachments[]" class="form-control" multiple>
    <small class="text-muted">Supported: PDF, JPG, PNG, DOCX (Max 5MB)</small>
    
    @if(isset($task) && $task->attachments_count > 0) 
        <div class="mt-2">
            <span class="badge bg-info">{{ $task->attachments_count }} files attached previously</span>
        </div>
    @endif
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('client.tasks.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary fw-bold px-4">
        <i class="fas fa-paper-plane me-2"></i> {{ isset($task) ? 'Update Request' : 'Submit Request' }}
    </button>
</div>

<script>
    // Pass PHP data to JS
    const allCategories = @json($categories);
    const selectedLvl1 = "{{ $selectedLvl1 ?? '' }}";
    const selectedLvl2 = "{{ $selectedLvl2 ?? '' }}";

    // Helper to find category by ID recursively
    function findCategory(id) {
        for (let main of allCategories) {
            if (main.id == id) return main;
            for (let sub1 of main.children) {
                if (sub1.id == id) return sub1;
                for (let sub2 of sub1.children) {
                    if (sub2.id == id) return sub2;
                }
            }
        }
        return null;
    }

    function loadLevel1() {
        const parentId = document.getElementById('cat_lvl_0').value;
        const lvl1Select = document.getElementById('cat_lvl_1');
        const lvl2Select = document.getElementById('cat_lvl_2');
        
        lvl1Select.innerHTML = '<option value="">-- Select --</option>';
        lvl2Select.innerHTML = '<option value="">-- Select --</option>';
        lvl1Select.disabled = true;
        lvl2Select.disabled = true;

        if (!parentId) return;

        const parentCat = allCategories.find(c => c.id == parentId);
        if (parentCat && parentCat.children) {
            parentCat.children.forEach(child => {
                let selected = (child.id == selectedLvl1) ? 'selected' : '';
                lvl1Select.innerHTML += `<option value="${child.id}" ${selected}>${child.name}</option>`;
            });
            lvl1Select.disabled = false;
            // If we have a selection (Edit Mode), trigger next level
            if(selectedLvl1) loadLevel2();
        }
    }

    function loadLevel2() {
        const mainId = document.getElementById('cat_lvl_0').value;
        const parentId = document.getElementById('cat_lvl_1').value;
        const lvl2Select = document.getElementById('cat_lvl_2');

        lvl2Select.innerHTML = '<option value="">-- Select --</option>';
        lvl2Select.disabled = true;

        if (!parentId) return;

        const mainCat = allCategories.find(c => c.id == mainId);
        const subCat = mainCat.children.find(c => c.id == parentId);

        if (subCat && subCat.children) {
            subCat.children.forEach(child => {
                let selected = (child.id == selectedLvl2) ? 'selected' : ''; // Use 'category_id' from model
                lvl2Select.innerHTML += `<option value="${child.id}" ${selected}>${child.name}</option>`;
            });
            lvl2Select.disabled = false;
        }
    }

    // Initialize on Load (For Edit Mode)
    document.addEventListener("DOMContentLoaded", function() {
        if(document.getElementById('cat_lvl_0').value) {
            loadLevel1();
        }
    });
</script>