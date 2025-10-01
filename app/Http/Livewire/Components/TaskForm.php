<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Task;

/**
 * TaskForm Component
 *
 * Handles creating and editing tasks in the Kanban board.
 * Provides form validation and data persistence for task operations.
 */
class TaskForm extends Component
{
    use AuthorizesRequests;

    /**
     * The ID of the task being edited, null for new tasks.
     *
     * @var int|null
     */
    public ?int $editingId = null;

    /**
     * The task title.
     *
     * @var string
     */
    public string $title = '';

    /**
     * The task description.
     *
     * @var string
     */
    public string $description = '';

    /**
     * The status for new tasks.
     *
     * @var string
     */
    public string $statusForNew = 'pending';

    /**
     * Whether the form is currently visible.
     *
     * @var bool
     */
    public bool $showForm = false;

    /**
     * Livewire event listeners.
     *
     * @var array<string, string>
     */
    protected $listeners = [
        'startCreate' => 'startCreate',
        'startEdit' => 'startEdit',
        'hideForm' => 'hideForm'
    ];

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view('livewire.components.task-form');
    }

    /**
     * Initialize the form for creating a new task.
     *
     * @param string $status The initial status for the new task
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return void
     */
    public function startCreate(string $status = 'pending'): void
    {
        $this->authorize('create', Task::class);
        $this->resetForm();
        $this->statusForNew = $status;
        $this->showForm = true;
    }

    /**
     * Initialize the form for editing an existing task.
     *
     * @param int $taskId The ID of the task to edit
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return void
     */
    public function startEdit(int $taskId): void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $this->authorize('update', $task);

        $this->editingId = $task->id;
        $this->title = $task->title;
        $this->description = (string) $task->description;
        $this->showForm = true;
    }

    /**
     * Create a new task with the form data.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
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
        $this->emit('refreshBoard');
        $this->dispatchBrowserEvent('notify', ['message' => 'Task created successfully']);
    }

    /**
     * Update the existing task with the form data.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
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
        $this->emit('refreshBoard');
        $this->dispatchBrowserEvent('notify', ['message' => 'Task updated successfully']);
    }

    /**
     * Cancel the current editing operation and hide the form.
     *
     * @return void
     */
    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    /**
     * Hide the form without saving.
     *
     * @return void
     */
    public function hideForm(): void
    {
        $this->showForm = false;
    }

    /**
     * Validate the current task data.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
    private function validateTaskData(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'title.required' => 'The title is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
        ]);
    }

    /**
     * Reset the form to its initial state.
     *
     * @return void
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = '';
        $this->statusForNew = 'pending';
        $this->showForm = false;
    }
}
