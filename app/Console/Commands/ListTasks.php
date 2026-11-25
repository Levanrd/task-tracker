<?php

namespace App\Console\Commands;

use App\Services\TaskStore;
use Illuminate\Console\Command;
use RuntimeException;

class ListTasks extends Command
{
    protected $signature = 'task:list {status?}';

    protected $description = 'List tasks, optionally filtered by status';

    public function handle(TaskStore $store): int
    {
        $status = $this->argument('status');
        $validFilters = ['todo', 'in-progress', 'done', 'not-done'];

        if ($status !== null && !in_array($status, $validFilters, true)) {
            $this->error('Use todo, in-progress, done, or not-done.');

            return self::FAILURE;
        }

        try {
            $tasks = $store->all();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $rows = [];

        foreach ($tasks as $task) {
            $matches = match ($status) {
                null => true,
                'not-done' => in_array($task['status'], ['todo', 'in-progress'], true),
                default => $task['status'] === $status,
            };

            if ($matches) {
                $rows[] = [$task['id'], $task['status'], $task['description']];
            }
        }

        if ($rows === []) {
            $this->info('No matching tasks.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Status', 'Description'], $rows);

        return self::SUCCESS;
    }
}
