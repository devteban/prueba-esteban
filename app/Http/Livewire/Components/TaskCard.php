<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use App\Models\Task;

/**
 * TaskCard Component
 *
 * Displays an individual task card with edit and delete functionality.
 * Handles user interactions for task management within the Kanban board.
 */
class TaskCard extends Component
{
    use AuthorizesRequests;

    /**
     * The task model instance.
     *
     * @var \App\Models\Task
     */
    public Task $task;

    /**
     * The background color class for the card.
     *
     * @var string
     */
    public string $cardBgColor;

    /**
     * Initialize the component with task data.
     *
     * @param \App\Models\Task $task The task to display
     * @param string $cardBgColor The CSS background color class
     * @return void
     */
    public function mount(Task $task, string $cardBgColor = 'bg-gray-50'): void
    {
        $this->task = $task;
        $this->cardBgColor = $cardBgColor;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View
    {
        return view('livewire.components.task-card');
    }

    /**
     * Emit an event to start editing this task.
     *
     * @return void
     */
    public function editTask(): void
    {
        $this->emit('startEdit', $this->task->id);
    }

    /**
     * Delete the current task after authorization.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return void
     */
    public function deleteTask(): void
    {
        $this->authorize('delete', $this->task);
        $this->task->delete();

        $this->emit('refreshColumn');
        $this->emit('refreshBoard');
        $this->dispatchBrowserEvent('notify', ['message' => 'Task deleted successfully']);
    }
}
