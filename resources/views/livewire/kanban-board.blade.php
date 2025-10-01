<div x-data="kanban()" x-init="init()" class="grid grid-cols-1 md:grid-cols-3 gap-4" @notify.window="alert($event.detail.message)">

    <!-- Pending Column -->
    <div class="bg-white rounded shadow p-3">
        <h3 class="font-semibold text-gray-800 mb-2">Pending</h3>
        <div class="flex items-center justify-between mb-2">
            <button class="text-xs px-2 py-1 bg-blue-600 text-white rounded" wire:click="addTask('pending')">
                Add
            </button>
        </div>
        <div id="col-pending" class="space-y-2 min-h-[200px] p-1 rounded border border-dashed border-gray-300">
            @foreach($this->getPendingTasks() as $task)
                <div class="border rounded p-2 bg-gray-50 cursor-move" data-id="{{ $task->id }}">
                    <div class="text-sm font-medium">{{ $task->title }}</div>
                    @if($task->description)
                        <div class="text-xs text-gray-600 mt-1">{{ $task->description }}</div>
                    @endif
                    <div class="mt-2 flex gap-2 text-xs">
                        <button class="px-2 py-0.5 bg-gray-200 rounded hover:bg-gray-300" wire:click="editTask({{ $task->id }})">
                            Edit
                        </button>
                        <button class="px-2 py-0.5 bg-red-600 text-white rounded hover:bg-red-700" wire:click="deleteTask({{ $task->id }})">
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="bg-white rounded shadow p-3">
        <h3 class="font-semibold text-gray-800 mb-2">In Progress</h3>
        <div class="flex items-center justify-between mb-2">
            <button class="text-xs px-2 py-1 bg-blue-600 text-white rounded" wire:click="addTask('in_progress')">
                Add
            </button>
        </div>
        <div id="col-in_progress" class="space-y-2 min-h-[200px] p-1 rounded border border-dashed border-gray-300">
            @foreach($this->getInProgressTasks() as $task)
                <div class="border rounded p-2 bg-yellow-50 cursor-move" data-id="{{ $task->id }}">
                    <div class="text-sm font-medium">{{ $task->title }}</div>
                    @if($task->description)
                        <div class="text-xs text-gray-600 mt-1">{{ $task->description }}</div>
                    @endif
                    <div class="mt-2 flex gap-2 text-xs">
                        <button class="px-2 py-0.5 bg-gray-200 rounded hover:bg-gray-300" wire:click="editTask({{ $task->id }})">
                            Edit
                        </button>
                        <button class="px-2 py-0.5 bg-red-600 text-white rounded hover:bg-red-700" wire:click="deleteTask({{ $task->id }})">
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Completed Column -->
    <div class="bg-white rounded shadow p-3">
        <h3 class="font-semibold text-gray-800 mb-2">Completed</h3>
        <div class="flex items-center justify-between mb-2">
            <button class="text-xs px-2 py-1 bg-blue-600 text-white rounded" wire:click="addTask('completed')">
                Add
            </button>
        </div>
        <div id="col-completed" class="space-y-2 min-h-[200px] p-1 rounded border border-dashed border-gray-300">
            @foreach($this->getCompletedTasks() as $task)
                <div class="border rounded p-2 bg-green-50 cursor-move" data-id="{{ $task->id }}">
                    <div class="text-sm font-medium">{{ $task->title }}</div>
                    @if($task->description)
                        <div class="text-xs text-gray-600 mt-1">{{ $task->description }}</div>
                    @endif
                    <div class="mt-2 flex gap-2 text-xs">
                        <button class="px-2 py-0.5 bg-gray-200 rounded hover:bg-gray-300" wire:click="editTask({{ $task->id }})">
                            Edit
                        </button>
                        <button class="px-2 py-0.5 bg-red-600 text-white rounded hover:bg-red-700" wire:click="deleteTask({{ $task->id }})">
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Formulario -->
    @if($showForm)
    <div class="md:col-span-3 bg-white rounded shadow p-4 mt-4">
        <h4 class="font-semibold mb-3">{{ $editingId ? 'Edit Task' : 'New Task' }}</h4>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2" wire:model.defer="title" placeholder="Task title">
                @error('title')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2" wire:model.defer="description" placeholder="Optional description">
            </div>
            @if(!$editingId)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2" wire:model="statusForNew">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            @endif
            <div class="flex gap-2">
                @if($editingId)
                    <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" wire:click="updateTask">
                        Update
                    </button>
                @else
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" wire:click="createTask">
                        Create
                    </button>
                @endif
                <button class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600" wire:click="cancelEdit">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    <script>
        function kanban() {
            return {
                init() {
                    // Wait for Livewire to be available
                    const initSortable = () => {
                        const statusMapping = {
                            'col-pending': 'pending',
                            'col-in_progress': 'in_progress',
                            'col-completed': 'completed'
                        };

                        ['col-pending', 'col-in_progress', 'col-completed'].forEach(colId => {
                        const element = document.getElementById(colId);
                        if (element) {
                            new Sortable(element, {
                                group: 'tasks',
                                animation: 150,
                                ghostClass: 'opacity-50',
                                chosenClass: 'shadow-lg',
                                dragClass: 'rotate-2',
                                onEnd: (evt) => {
                                    const taskId = parseInt(evt.item.getAttribute('data-id'));
                                    const toStatus = statusMapping[evt.to.id];
                                    const orderedIds = Array.from(evt.to.querySelectorAll('[data-id]'))
                                        .map(el => parseInt(el.getAttribute('data-id')));

                                    // Wait for Livewire to be available
                                    if (typeof Livewire !== 'undefined' && Livewire.find) {
                                        @this.call('handleTaskMoved', taskId, toStatus, orderedIds);
                                    } else {
                                        console.error('Livewire is not available');
                                    }
                                }
                            });
                        }
                    });
                    };

                    // Use helper function to wait for Livewire
                    if (typeof window.waitForLivewire === 'function') {
                        window.waitForLivewire(initSortable);
                    } else {
                        // Fallback if function is not available
                        setTimeout(initSortable, 500);
                    }
                }
            }
        }
    </script>
</div>
