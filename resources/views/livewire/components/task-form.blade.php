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
                <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" wire:click="updateTask" wire:loading.attr="disabled">
                    <span wire:loading.remove>Update</span>
                    <span wire:loading>Updating...</span>
                </button>
            @else
                <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" wire:click="createTask" wire:loading.attr="disabled">
                    <span wire:loading.remove>Create</span>
                    <span wire:loading>Creating...</span>
                </button>
            @endif
            <button class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600" wire:click="cancelEdit">Cancel</button>
        </div>
    </div>
</div>
@endif
