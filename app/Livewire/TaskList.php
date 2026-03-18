<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class TaskList extends Component
{
    public string $filter = 'all';   // all | completed | important
    public string $search = '';

    // ─── Listeners ─────────────────────────────────
    #[On('taskSaved')]
    public function refreshTasks(): void {}

    // ─── Reorder (SortableJS) ──────────────────────
    #[On('tasksReordered')]
    public function reorderTasks(array $order): void
    {
        foreach ($order as $item) {
            Task::where('id', $item['id'])
                ->where('user_id', Auth::id())
                ->update(['order_index' => $item['order']]);
        }
    }

    // ─── Toggle complete ───────────────────────────
    public function toggleComplete(int $taskId): void
    {
        $task = Task::forUser(Auth::id())->findOrFail($taskId);
        $task->status = $task->isCompleted() ? 'pending' : 'completed';
        $task->save();
    }

    // ─── Delete ────────────────────────────────────
    public function deleteTask(int $taskId): void
    {
        $task = Task::forUser(Auth::id())->findOrFail($taskId);
        $task->delete();
    }

    // ─── Search (called from Navbar via event) ─────
    #[On('searchUpdated')]
    public function updateSearch(string $term): void
    {
        $this->search = $term;
    }

    // ─── Render ────────────────────────────────────
    public function render()
    {
        $query = Task::forUser(Auth::id())
            ->orderBy('order_index')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->search($this->search);
        }

        match ($this->filter) {
            'completed' => $query->completed(),
            'important' => $query->important(),
            default     => null,
        };

        $tasks = $query->get();

        $counts = [
            'all'       => Task::forUser(Auth::id())->count(),
            'completed' => Task::forUser(Auth::id())->completed()->count(),
            'important' => Task::forUser(Auth::id())->important()->count(),
            'pending'   => Task::forUser(Auth::id())->pending()->count(),
        ];

        return view('livewire.task-list', compact('tasks', 'counts'));
    }
}
