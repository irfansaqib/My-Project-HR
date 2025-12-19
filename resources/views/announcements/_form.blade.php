<div class="mb-3">
    <label class="form-label fw-bold">Title</label>
    <input type="text" name="title" class="form-control" 
           value="{{ old('title', $announcement->title ?? '') }}" 
           placeholder="e.g. System Maintenance" required>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Message</label>
    <textarea name="message" class="form-control" rows="4" 
              placeholder="Enter details..." required>{{ old('message', $announcement->message ?? '') }}</textarea>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Type / Color</label>
        <select name="type" class="form-select">
            @php $currentType = old('type', $announcement->type ?? 'info'); @endphp
            <option value="info" {{ $currentType == 'info' ? 'selected' : '' }}>Info (Blue)</option>
            <option value="warning" {{ $currentType == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
            <option value="danger" {{ $currentType == 'danger' ? 'selected' : '' }}>Urgent (Red)</option>
            <option value="success" {{ $currentType == 'success' ? 'selected' : '' }}>Success (Green)</option>
        </select>
    </div>

    <div class="col-md-6 mb-3 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" id="clientVisible" name="is_client_visible" value="1"
                {{ old('is_client_visible', $announcement->is_client_visible ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="clientVisible">
                Show on Client Portal?
            </label>
            <div class="form-text small">If unchecked, only employees can see this.</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label small text-muted">Schedule Start (Optional)</label>
        <input type="datetime-local" name="start_date" class="form-control"
               value="{{ old('start_date', isset($announcement->start_date) ? $announcement->start_date->format('Y-m-d\TH:i') : '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label small text-muted">Auto-Expire (Optional)</label>
        <input type="datetime-local" name="end_date" class="form-control"
               value="{{ old('end_date', isset($announcement->end_date) ? $announcement->end_date->format('Y-m-d\TH:i') : '') }}">
    </div>
</div>

<button type="submit" class="btn btn-primary w-100">
    <i class="fas fa-save me-1"></i> {{ isset($announcement) ? 'Update Announcement' : 'Publish Announcement' }}
</button>