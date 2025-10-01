<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Task;

/**
 * KanbanColumn Component
 *
 * Represents a single column in the Kanban board (Pending, In Progress, Completed).
 * Manages tasks within the column and handles column-specific operations.
 */
class KanbanColumn extends Component
{
    use AuthorizesRequests;

    /**
     * The status identifier for this column.
     *
     * @var string
     */
    public string $status;

    /**
     * The display title for this column.
     *
     * @var string
     */
    public string $title;

    /**
     * The background color class for task cards in this column.
     *
     * @var string
     */
    public string $bgColor;

    /**
     * The collection of tasks in this column.
     *
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task>
     */
    public Collection $tasks;

    /**
     * Livewire event listeners.
     *
     * @var array<string, string>
     */
    protected $listeners = ['refreshColumn' => '$refresh'];

    /**
     * Initialize the component with column configuration.
     *
     * @param string $status The status identifier (pending, in_progress, completed)
     * @param string $title The display title for the column
     * @param string $bgColor The CSS background color class for task cards
     * @return void
     */
    public function mount(string $status, string $title, string $bgColor = 'bg-gray-50'): void
    {
        $this->status = $status;
        $this->title = $title;
        $this->bgColor = $bgColor;
        $this->loadTasks();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view('livewire.components.kanban-column');
    }

    /**
     * Load tasks for this column from the database.
     *
     * @return void
     */
    public function loadTasks(): void
    {
        $this->tasks = Task::where('user_id', Auth::id())
            ->where('status', $this->status)
            ->orderBy('order')
            ->get();
    }

    /**
     * Emit an event to start creating a new task in this column.
     *
     * @return void
     */
    public function addTask(): void
    {
        $this->emit('startCreate', $this->status);
    }

    /**
     * Emit an event to start editing a specific task.
     *
     * @param int $taskId The ID of the task to edit
     * @return void
     */
    public function editTask(int $taskId): void
    {
        $this->emit('startEdit', $taskId);
    }

    /**
     * Delete a task from this column.
     *
     * @param int $taskId The ID of the task to delete
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return void
     */
    public function deleteTask(int $taskId): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $this->authorize('delete', $task);
        $task->delete();

        $this->loadTasks();
        $this->emit('refreshBoard');
        $this->dispatchBrowserEvent('notify', ['message' => 'Task deleted successfully']);
    }
}
