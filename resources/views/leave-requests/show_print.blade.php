<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Extra Leave Request - {{ $leaveRequest->employee->name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #fff; font-size: 12pt; }
        .print-container { max-width: 100%; width: 210mm; margin: auto; padding: 10mm; font-family: 'Times New Roman', Times, serif; }
        .signature-section { position: relative; }
        .signature-line { border-top: 1px solid #000; margin-top: 60px; width: 250px; }
        .stamp {
            position: absolute;
            top: 20px;
            left: 10px;
            border: 5px solid #008000;
            color: #008000;
            font-size: 2rem;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 10px;
            transform: rotate(-15deg);
            opacity: 0.5;
            text-transform: uppercase;
        }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="text-right mb-4 no-print">
            <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Back to List</a>
            <button onclick="window.print()" class="btn btn-primary">Print</button>
        </div>
        <h3 class="text-center mb-5">Extra Leave Application</h3>
        <p><strong>Date:</strong> {{ $leaveRequest->created_at->format('d M, Y') }}</p>
        <p class="mt-4">Dear Sir,</p>
        <p>Respectfully, it is stated that I have availed all my available leaves. Due to reasons specified below, I require extra leave(s).</p>
        <p>It is requested that you kindly allot me extra leaves from <strong>{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M, Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M, Y') }}</strong>.</p>
        
        <div class="card mt-4">
            <div class="card-header"><strong>Reason Provided:</strong></div>
            <div class="card-body">
                <p>{{ $leaveRequest->reason }}</p>
            </div>
        </div>

        <p class="mt-4">I shall be grateful if you kindly accept my request.</p>
        <div class="mt-5">
            <p>Yours sincerely,</p>
            <div class="signature-line"></div>
            <p><strong>{{ $leaveRequest->employee->name }}</strong><br>
            <em>{{ $leaveRequest->employee->designation }}</em></p>
        </div>
        <div class="mt-5 signature-section">
            <div class="stamp">Approved</div>
            <p>Approved / Rejected by:</p>
            <div class="signature-line"></div>
            <p><strong>Manager</strong></p>
        </div>
    </div>
</body>
</html>