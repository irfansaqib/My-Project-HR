<x-mail::message>
# Your Payslip is Ready

Dear {{ $payslip->employee->name }},

Your payslip for the month of **{{ $payslip->month }}, {{ $payslip->year }}** is attached to this email.

Thanks,<br>
{{ $business->name }}
</x-mail::message>