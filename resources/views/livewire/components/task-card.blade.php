<div class="border rounded p-2 {{ $cardBgColor }} cursor-move" data-id="{{ $task->id }}">
    <div class="text-sm font-medium">{{ $task->title }}</div>
    @if($task->description)
        <div class="text-xs text-gray-600 mt-1">{{ $task->description }}</div>
    @endif
    <div class="mt-2 flex gap-2 text-xs">
        <button class="px-2 py-0.5 bg-gray-200 rounded hover:bg-gray-300" wire:click="editTask">
            Edit
        </button>
        <button class="px-2 py-0.5 bg-red-600 text-white rounded hover:bg-red-700" wire:click="deleteTask">
            Delete
        </button>
    </div>
</div>
