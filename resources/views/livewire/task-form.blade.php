<div>
    @if($isOpen)
    <div class="modal-overlay" wire:click.self="close">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">
                    {{ $taskId ? '✏️ Edit Task' : '✨ New Task' }}
                </div>
                <button class="modal-close" wire:click="close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <form wire:submit="save">
                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label">Task Title *</label>
                    <input type="text" class="form-input" wire:model="title"
                           placeholder="What needs to be done?" autofocus>
                    @error('title') <div class="form-error">⚠ {{ $message }}</div> @enderror
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" wire:model="description"
                              placeholder="Add more details (optional)…"></textarea>
                    @error('description') <div class="form-error">⚠ {{ $message }}</div> @enderror
                </div>

                {{-- Due date + Priority --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-input" wire:model="due_date">
                        @error('due_date') <div class="form-error">⚠ {{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select class="form-select" wire:model="priority">
                            <option value="low">🟢 Low</option>
                            <option value="medium">🟡 Medium</option>
                            <option value="high">🔴 High</option>
                        </select>
                        @error('priority') <div class="form-error">⚠ {{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Status (only on edit) --}}
                @if($taskId)
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model="status">
                        <option value="pending">⏳ Pending</option>
                        <option value="completed">✅ Completed</option>
                    </select>
                </div>
                @endif

                {{-- Actions --}}
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" wire:click="close">Cancel</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ $taskId ? 'Save Changes' : 'Create Task' }}</span>
                        <span wire:loading>Saving…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
