<div class="bg-white rounded shadow p-3">
    <h3 class="font-semibold text-gray-800 mb-2">{{ $title }}</h3>
    <div class="flex items-center justify-between mb-2">
        <button class="text-xs px-2 py-1 bg-blue-600 text-white rounded" wire:click="addTask">
            Add
        </button>
    </div>

    <div id="col-{{ $status }}" class="space-y-2 min-h-[200px] p-1 rounded border border-dashed border-gray-300">
        @foreach ($tasks as $task)
            @livewire('components.task-card', [
                'task' => $task,
                'cardBgColor' => $bgColor
            ], 'task-card-' . $task->id)
        @endforeach
    </div>
</div>
