@extends('layouts.admin')
@section('title', 'Attendance Calendar')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <style>
        .fc-daygrid-day.fc-day-today { 
            background-color: #fffbeb !important;
        }
        .fc-event {
            padding: 2px 5px !important;
            font-size: 0.8em !important;
            text-align: center;
             /* ✅ ADDED to support multi-line text */
            white-space: pre-line !important; 
        }

        /* Styling for the text events */
        .fc-event-holiday-text, .fc-event-offday-text {
            background-color: transparent !important;
            border: none !important;
        }

        /* ✅ UPDATED font-size and font-weight */
        .fc-event.fc-event-holiday-text .fc-event-title {
            color: #0d6efd !important; /* Dark Blue Text */
            font-weight: 700 !important; /* Bold */
            font-size: 0.9em !important; /* Larger Text */
        }
        /* ✅ UPDATED font-size and font-weight */
        .fc-event.fc-event-offday-text .fc-event-title {
            color: #198754 !important; /* Dark Green Text */
            font-weight: 700 !important; /* Bold */
            font-size: 0.9em !important; /* Larger Text */
        }
        
        #calendar-prompt { color: #6c757d; font-style: italic; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title" id="calendar-title">Team Attendance Calendar</h3>
        <div class="w-25">
            <select id="employee_id_filter" class="form-control selectpicker" data-live-search="true" title="Select an Employee...">
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body">
        <div id='calendar'>
            <div class="text-center p-5" id="calendar-prompt">
                <p>Please select an employee from the dropdown to view their attendance calendar.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var employeeFilter = document.getElementById('employee_id_filter');
    var calendarTitle = document.getElementById('calendar-title');
    var calendarPrompt = document.getElementById('calendar-prompt');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            var employeeId = employeeFilter.value;
            if (!employeeId) {
                successCallback([]); return;
            }
            var url = new URL('{{ route("api.calendar-events") }}');
            url.searchParams.append('start', fetchInfo.startStr);
            url.searchParams.append('end', fetchInfo.endStr);
            url.searchParams.append('employee_id', employeeId);
            fetch(url).then(res => res.json()).then(data => successCallback(data)).catch(err => failureCallback(err));
        },
        eventDidMount: function(info) {
            $(info.el).tooltip({ title: info.event.title.replace("\n", " "), placement: 'top', trigger: 'hover', container: 'body' });
        },
    });

    employeeFilter.addEventListener('change', function() {
        var selectedEmployeeName = this.options[this.selectedIndex].text;
        if (this.value) {
            calendarTitle.textContent = selectedEmployeeName + "'s Attendance Calendar";
            if(calendarPrompt) calendarPrompt.style.display = 'none';
            calendar.refetchEvents();
        } else {
            calendarTitle.textContent = "Team Attendance Calendar";
            if(calendarPrompt) calendarPrompt.style.display = 'block';
            calendar.removeAllEvents();
        }
    });

    calendar.render();
});
</script>
@endpush