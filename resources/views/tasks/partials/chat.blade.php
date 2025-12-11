{{-- CHAT HISTORY --}}
<div style="height: 400px; overflow-y: auto;" class="mb-3 pr-2">
    {{-- ORIGINAL TASK DESCRIPTION --}}
    <div class="media mb-4">
        <div class="mr-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="media-body">
            <div class="bg-white p-3 rounded shadow-sm border">
                <h6 class="mt-0 font-weight-bold text-primary">{{ $task->creator->name ?? 'System' }} <small class="text-muted ml-2">Created Request</small></h6>
                <p class="mb-0">{{ $task->description }}</p>
                <small class="text-muted d-block mt-2"><i class="far fa-clock mr-1"></i> {{ $task->created_at->format('d M Y, h:i A') }}</small>
            </div>
        </div>
    </div>

    {{-- MESSAGES LOOP --}}
    @foreach($task->messages as $msg)
        @php $isMe = $msg->sender_id == Auth::id(); @endphp
        <div class="media mb-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
            <img src="https://ui-avatars.com/api/?name={{ $msg->sender->name }}&background=random" class="{{ $isMe ? 'ml-3' : 'mr-3' }} rounded-circle" width="40" alt="{{ $msg->sender->name }}">
            
            <div class="media-body {{ $isMe ? 'text-right' : '' }}">
                <div class="{{ $isMe ? 'bg-info text-white' : 'bg-white border' }} p-3 rounded shadow-sm d-inline-block text-left" style="max-width: 85%;">
                    <strong class="d-block mb-1 small {{ $isMe ? 'text-white-50' : 'text-muted' }}">{{ $msg->sender->name }}</strong>
                    {{ $msg->message }}
                    
                    @if($msg->attachment_path)
                        <div class="mt-2 pt-2 border-top {{ $isMe ? 'border-white-50' : 'border-light' }}">
                            <a href="{{ Storage::url($msg->attachment_path) }}" target="_blank" class="{{ $isMe ? 'text-white' : 'text-primary' }} small">
                                <i class="fas fa-paperclip mr-1"></i> View Attachment
                            </a>
                        </div>
                    @endif
                </div>
                <small class="text-muted d-block mt-1">{{ $msg->created_at->format('d M, h:i A') }}</small>
            </div>
        </div>
    @endforeach
</div>

{{-- SEND BOX --}}
<div class="border-top pt-3">
    <form action="{{ route('tasks.messages.store', $task->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="input-group">
            <input type="text" name="message" class="form-control" placeholder="Type a message..." required>
            <div class="input-group-append">
                <label class="btn btn-light border mb-0" title="Attach File" style="cursor: pointer;">
                    <i class="fas fa-paperclip text-secondary"></i> 
                    <input type="file" name="attachment" hidden onchange="this.parentElement.classList.add('btn-warning')">
                </label>
                <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </form>
</div>