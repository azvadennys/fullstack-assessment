<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskComment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create 5 Users
        $users = User::factory(5)->create();

        // Create Admin specific for login test
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
        $users->push($admin);

        // 2. Create 15 Tasks
        // Kita loop manual agar bisa assign foreign keys dengan benar
        for ($i = 0; $i < 15; $i++) {
            $task = Task::create([
                'title' => 'Task Project ' . ($i + 1),
                'description' => 'Description for task number ' . ($i + 1),
                'status' => collect(['pending', 'in_progress', 'completed'])->random(),
                'priority' => collect(['low', 'medium', 'high'])->random(),
                'assigned_user_id' => $users->random()->id,
                'created_by' => $users->random()->id,
                'due_date' => now()->addDays(rand(1, 30)),
            ]);

            // 3. Create Comments (10 comments distributed randomly)
            if ($i < 10) {
                TaskComment::create([
                    'task_id' => $task->id,
                    'user_id' => $users->random()->id,
                    'comment' => 'This is a sample comment for task ' . $task->id,
                ]);
            }
        }
    }
}
