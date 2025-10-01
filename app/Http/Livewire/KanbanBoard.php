<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Task;

class KanbanBoard extends Component
{
    use AuthorizesRequests;

    public ?int $editingId = null;
    public string $title = '';
    public string $description = '';
    public string $statusForNew = 'pending';
    public bool $showForm = false;

    protected $listeners = [
        'taskMoved' => 'handleTaskMoved',
        'listReordered' => 'handleListReordered',
        'refreshBoard' => '$refresh'
    ];

    public function mount(): void
    {
    }

    public function getPendingTasks()
    {
        return Task::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->orderBy('order')
            ->get();
    }

    public function getInProgressTasks()
    {
        return Task::where('user_id', Auth::id())
            ->where('status', 'in_progress')
            ->orderBy('order')
            ->get();
    }

    public function getCompletedTasks()
    {
        return Task::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->orderBy('order')
            ->get();
    }

    public function addTask(string $status = 'pending'): void
    {
        $this->authorize('create', Task::class);
        $this->resetForm();
        $this->statusForNew = $status;
        $this->showForm = true;
    }

    public function editTask(int $taskId): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $this->authorize('update', $task);

        $this->editingId = $task->id;
        $this->title = $task->title;
        $this->description = (string) $task->description;
        $this->showForm = true;
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $this->authorize('delete', $task);

        $task->delete();
        $this->dispatchBrowserEvent('notify', ['message' => 'Task deleted successfully']);
    }

    public function createTask(): void
    {
        $this->authorize('create', Task::class);
        $this->validateTaskData();

        $userId = Auth::id();
        $maxOrder = Task::where('user_id', $userId)
            ->where('status', $this->statusForNew)
            ->max('order') ?? 0;

        Task::create([
            'user_id' => $userId,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'status' => $this->statusForNew,
            'order' => $maxOrder + 1,
        ]);

        $this->resetForm();
        $this->dispatchBrowserEvent('notify', ['message' => 'Task created successfully']);
    }

    public function updateTask(): void
    {
        if ($this->editingId === null) {
            return;
        }

        $task = Task::where('user_id', Auth::id())->findOrFail($this->editingId);
        $this->authorize('update', $task);

        $this->validateTaskData();

        $task->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
        ]);

        $this->resetForm();
        $this->dispatchBrowserEvent('notify', ['message' => 'Task updated successfully']);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function handleTaskMoved(int $taskId, string $toStatus, array $orderedIds = []): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $this->authorize('update', $task);

        $task->update(['status' => $toStatus]);

        foreach ($orderedIds as $index => $id) {
            Task::where('user_id', Auth::id())
                ->where('id', $id)
                ->update(['order' => $index + 1]);
        }

        $this->dispatchBrowserEvent('notify', ['message' => 'Task moved successfully']);
    }

    public function handleListReordered(string $status, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $taskId) {
            $task = Task::where('user_id', Auth::id())->find($taskId);
            if ($task && $task->status === $status) {
                $this->authorize('update', $task);
                $task->update(['order' => $index + 1]);
            }
        }
    }

    public function render(): View
    {
        return view('livewire.kanban-board');
    }

    private function validateTaskData(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'title.required' => 'The title is required.',
            'title.max' => 'The title cannot be longer than 255 characters.',
            'description.max' => 'The description cannot be longer than 1000 characters.',
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->statusForNew = 'pending';
        $this->showForm = false;
    }
}
