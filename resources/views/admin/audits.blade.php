<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Task Audit
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600">
                                    <th class="p-2">Date</th>
                                    <th class="p-2">User</th>
                                    <th class="p-2">Event</th>
                                    <th class="p-2">Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($audits as $audit)
                                    <tr class="border-t">
                                        <td class="p-2">{{ $audit->created_at }}</td>
                                        <td class="p-2">{{ optional($audit->user)->email ?? 'system' }}</td>
                                        <td class="p-2">{{ $audit->event }}</td>
                                        <td class="p-2 font-mono text-xs">
                                            <pre class="whitespace-pre-wrap">{{ json_encode($audit->getModified(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-4 text-gray-500" colspan="4">No audits found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
