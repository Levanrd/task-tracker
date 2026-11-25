<?php

namespace App\Console\Commands;

use App\Services\TaskStore;
use Illuminate\Console\Command;
use JsonException;
use RuntimeException;

class AddTask extends Command
{
    protected $signature = 'task:add {description}';

    protected $description = 'Add a task';

    public function handle(TaskStore $store): int
    {
        $description = trim($this->argument('description'));

        if ($description === '') {
            $this->error('Enter a task description.');

            return self::FAILURE;
        }

        try {
            $id = $store->add($description);
        } catch (RuntimeException | JsonException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Task added successfully (ID: {$id}).");

        return self::SUCCESS;
    }
}
