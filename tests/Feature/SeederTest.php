<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoTasksSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_user_seeder_creates_admin_user()
    {
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'is_admin' => true,
        ]);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertNotEmpty($admin->password);
        $this->assertNotEquals('password', $admin->password); // Debe estar hasheada
    }

    /** @test */
    public function admin_user_seeder_updates_existing_admin_user()
    {
        // Crear un usuario admin existente con datos diferentes
        $existingAdmin = User::create([
            'email' => 'admin@example.com',
            'name' => 'Old Admin Name',
            'password' => bcrypt('old-password'),
            'is_admin' => false,
        ]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        // Debe actualizar los datos
        $this->assertEquals('Admin', $admin->name);
        $this->assertTrue($admin->is_admin);
        $this->assertEquals($existingAdmin->id, $admin->id); // Mismo ID, actualizado
    }

    /** @test */
    public function demo_tasks_seeder_creates_tasks_for_admin()
    {
        // Primero crear el admin
        $this->seed(AdminUserSeeder::class);

        // Luego crear las tareas de demo
        $this->seed(DemoTasksSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        // Verificar que se crearon las tareas de demo
        $this->assertDatabaseHas('tasks', [
            'user_id' => $admin->id,
            'title' => 'Setup Project',
            'description' => 'Create Laravel base with Breeze',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $admin->id,
            'title' => 'Design Task Model',
            'description' => 'Fields and migrations',
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $admin->id,
            'title' => 'Kanban Board',
            'description' => 'Livewire component with drag & drop',
            'status' => 'completed',
        ]);

        // Verificar que el admin tiene exactamente 3 tareas
        $this->assertEquals(3, $admin->tasks()->count());
    }

    /** @test */
    public function demo_tasks_seeder_does_nothing_without_admin()
    {
        // Ejecutar el seeder sin crear primero el admin
        $this->seed(DemoTasksSeeder::class);

        // No debe crear ninguna tarea
        $this->assertEquals(0, Task::count());
    }

    /** @test */
    public function demo_tasks_seeder_updates_existing_tasks()
    {
        // Crear admin
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('email', 'admin@example.com')->first();

        // Crear una tarea existente con el mismo título
        $existingTask = Task::create([
            'user_id' => $admin->id,
            'title' => 'Setup Project',
            'description' => 'Old description',
            'status' => 'completed',
            'order' => 5,
        ]);

        // Ejecutar el seeder
        $this->seed(DemoTasksSeeder::class);

        // Verificar que se actualizó la tarea existente
        $updatedTask = Task::where('id', $existingTask->id)->first();
        $this->assertEquals('Create Laravel base with Breeze', $updatedTask->description);
        $this->assertEquals('pending', $updatedTask->status);
        $this->assertEquals(1, $updatedTask->order);

        // Debe seguir siendo la misma tarea (mismo ID)
        $this->assertEquals($existingTask->id, $updatedTask->id);

        // El admin debe tener exactamente 3 tareas (no duplicadas)
        $this->assertEquals(3, $admin->tasks()->count());
    }

    /** @test */
    public function database_seeder_runs_all_seeders_in_correct_order()
    {
        $this->seed(DatabaseSeeder::class);

        // Verificar que se creó el admin
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $admin = User::where('email', 'admin@example.com')->first();

        // Verificar que se crearon las tareas de demo
        $this->assertEquals(3, $admin->tasks()->count());

        // Verificar que hay una tarea de cada estado
        $this->assertEquals(1, $admin->tasks()->where('status', 'pending')->count());
        $this->assertEquals(1, $admin->tasks()->where('status', 'in_progress')->count());
        $this->assertEquals(1, $admin->tasks()->where('status', 'completed')->count());
    }

    /** @test */
    public function seeders_can_be_run_multiple_times_safely()
    {
        // Ejecutar seeders múltiples veces
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        // Debe haber solo un admin
        $this->assertEquals(1, User::where('email', 'admin@example.com')->count());

        $admin = User::where('email', 'admin@example.com')->first();

        // Debe haber solo 3 tareas de demo (no duplicadas)
        $this->assertEquals(3, $admin->tasks()->count());
    }

    /** @test */
    public function seeder_creates_tasks_with_correct_order_values()
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $tasks = $admin->tasks()->get();

        // Todas las tareas deben tener order = 1
        foreach ($tasks as $task) {
            $this->assertEquals(1, $task->order);
        }
    }

    /** @test */
    public function seeder_tasks_have_valid_statuses()
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $tasks = $admin->tasks()->get();

        $validStatuses = ['pending', 'in_progress', 'completed'];

        foreach ($tasks as $task) {
            $this->assertContains($task->status, $validStatuses);
        }
    }

    /** @test */
    public function admin_user_has_correct_password_hash()
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        // Verificar que la contraseña está hasheada correctamente
        $this->assertTrue(\Hash::check('password', $admin->password));
    }

    /** @test */
    public function seeder_can_be_called_via_artisan_command()
    {
        Artisan::call('db:seed', ['--class' => DatabaseSeeder::class]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertEquals(3, $admin->tasks()->count());
    }
}
