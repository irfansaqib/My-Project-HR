<div class="card-body">
    <div class="form-group">
        <label for="name">Category Name <span class="text-danger">*</span></label>
        {{-- Logic: Use 'old' input if validation failed, otherwise use database value, otherwise empty --}}
        <input type="text" name="name" id="name" class="form-control" 
               value="{{ old('name', $category->name) }}" required>
    </div>

    <div class="form-group">
        <label for="parent_id">Parent Category (Optional)</label>
        <select name="parent_id" id="parent_id" class="form-control select2">
            <option value="">-- No Parent (Root Category) --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" 
                    {{-- Select if it matches the current parent --}}
                    {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                    {{ $cat->full_path ?? $cat->name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Select a parent to make this a Sub-Category (e.g., Taxation > <b>Compliance</b>)</small>
    </div>
</div>

<div class="card-footer text-right bg-white">
    <a href="{{ route('task-categories.index') }}" class="btn btn-secondary mr-2">Cancel</a>
    <button type="submit" class="btn btn-primary px-4">
        {{ $buttonText ?? 'Save Category' }}
    </button>
</div>