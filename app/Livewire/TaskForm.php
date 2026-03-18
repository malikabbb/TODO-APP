<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskForm extends Component
{
    public bool $isOpen    = false;
    public ?int $taskId    = null;

    // Form fields
    public string  $title       = '';
    public string  $description = '';
    public string  $due_date    = '';
    public string  $priority    = 'medium';
    public string  $status      = 'pending';

    protected function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date'    => ['nullable', 'date'],
            'priority'    => ['required', 'in:low,medium,high'],
            'status'      => ['required', 'in:pending,completed'],
        ];
    }

    // ─── Listen for FAB / edit clicks ─────────────
    #[On('openCreateModal')]
    public function openCreate(): void
    {
        $this->reset(['taskId', 'title', 'description', 'due_date', 'priority', 'status']);
        $this->priority = 'medium';
        $this->status   = 'pending';
        $this->isOpen   = true;
    }

    #[On('openEditModal')]
    public function openEdit(int $taskId): void
    {
        $task = Task::forUser(Auth::id())->findOrFail($taskId);
        $this->taskId      = $task->id;
        $this->title       = $task->title;
        $this->description = $task->description ?? '';
        $this->due_date    = $task->due_date?->format('Y-m-d') ?? '';
        $this->priority    = $task->priority;
        $this->status      = $task->status;
        $this->isOpen      = true;
    }

    // ─── Close modal ──────────────────────────────
    public function close(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    // ─── Save (create or update) ──────────────────
    public function save(): void
    {
        $this->validate();

        $maxOrder = Task::forUser(Auth::id())->max('order_index') ?? 0;

        if ($this->taskId) {
            $task = Task::forUser(Auth::id())->findOrFail($this->taskId);
            $task->update([
                'title'       => $this->title,
                'description' => $this->description ?: null,
                'due_date'    => $this->due_date ?: null,
                'priority'    => $this->priority,
                'status'      => $this->status,
            ]);
        } else {
            Task::create([
                'user_id'     => Auth::id(),
                'title'       => $this->title,
                'description' => $this->description ?: null,
                'due_date'    => $this->due_date ?: null,
                'priority'    => $this->priority,
                'status'      => $this->status,
                'order_index' => $maxOrder + 1,
            ]);
        }

        $isUpdate = (bool) $this->taskId;
        $this->isOpen = false;
        $this->dispatch('taskSaved');
        $this->dispatch('notify', message: $isUpdate ? 'Task updated successfully!' : 'Task created successfully!');
    }

    public function render()
    {
        return view('livewire.task-form');
    }
}
