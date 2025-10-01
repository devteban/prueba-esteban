<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;

class DemoTasksSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            return;
        }

        $tasks = [
            ['title' => 'Setup Project', 'description' => 'Create Laravel base with Breeze', 'status' => 'pending', 'order' => 1],
            ['title' => 'Design Task Model', 'description' => 'Fields and migrations', 'status' => 'in_progress', 'order' => 1],
            ['title' => 'Kanban Board', 'description' => 'Livewire component with drag & drop', 'status' => 'completed', 'order' => 1],
        ];

        foreach ($tasks as $data) {
            Task::updateOrCreate(
                ['user_id' => $admin->id, 'title' => $data['title']],
                array_merge($data, ['user_id' => $admin->id])
            );
        }
    }
}
