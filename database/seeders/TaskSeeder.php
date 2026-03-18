<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) return;

        $tasks = [
            // High priority - pending
            ['title' => 'Design system architecture', 'description' => 'Plan microservices layout and API contracts.', 'due_date' => now()->addDays(2)->format('Y-m-d'), 'priority' => 'high',   'status' => 'pending',   'order_index' => 1],
            ['title' => 'Fix critical login bug',     'description' => 'Users are unable to reset password via email.',       'due_date' => now()->addDay()->format('Y-m-d'),  'priority' => 'high',   'status' => 'pending',   'order_index' => 2],
            ['title' => 'Deploy to production',       'description' => 'Run zero-downtime deployment pipeline.',              'due_date' => now()->addDays(3)->format('Y-m-d'), 'priority' => 'high',   'status' => 'pending',   'order_index' => 3],

            // Medium priority - pending
            ['title' => 'Write unit tests',           'description' => 'Cover TaskList, TaskForm, and auth controllers.',     'due_date' => now()->addDays(5)->format('Y-m-d'), 'priority' => 'medium', 'status' => 'pending',   'order_index' => 4],
            ['title' => 'Update API documentation',   'description' => 'Swagger docs for v2 endpoints.',                     'due_date' => now()->addDays(7)->format('Y-m-d'), 'priority' => 'medium', 'status' => 'pending',   'order_index' => 5],
            ['title' => 'Code review – auth module',  'description' => null,                                                  'due_date' => now()->addDays(4)->format('Y-m-d'), 'priority' => 'medium', 'status' => 'pending',   'order_index' => 6],

            // Low priority - pending
            ['title' => 'Refactor CSS utilities',     'description' => 'Extract repetitive styles into reusable classes.',   'due_date' => now()->addDays(10)->format('Y-m-d'), 'priority' => 'low',   'status' => 'pending',   'order_index' => 7],
            ['title' => 'Update dependencies',        'description' => 'Run npm audit fix and composer update.',              'due_date' => null,                               'priority' => 'low',   'status' => 'pending',   'order_index' => 8],

            // Completed tasks
            ['title' => 'Set up MySQL database',      'description' => 'Created schema, user, and granted privileges.',      'due_date' => now()->subDays(2)->format('Y-m-d'), 'priority' => 'high',   'status' => 'completed', 'order_index' => 9],
            ['title' => 'Configure Laravel project',  'description' => 'Installed Livewire, Tailwind, and base packages.',   'due_date' => now()->subDays(3)->format('Y-m-d'), 'priority' => 'medium', 'status' => 'completed', 'order_index' => 10],
            ['title' => 'Create wireframes',          'description' => 'UI wireframes approved by stakeholders.',            'due_date' => now()->subDays(5)->format('Y-m-d'), 'priority' => 'low',    'status' => 'completed', 'order_index' => 11],
            ['title' => 'Initial project planning',   'description' => 'Sprint 1 planning meeting completed.',               'due_date' => now()->subDays(7)->format('Y-m-d'), 'priority' => 'medium', 'status' => 'completed', 'order_index' => 12],
        ];

        foreach ($tasks as $task) {
            Task::create(array_merge($task, ['user_id' => $user->id]));
        }
    }
}
