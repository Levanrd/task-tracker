<?php

namespace App\Services;

use Closure;
use JsonException;
use RuntimeException;

class TaskStore
{
    private const FILE = 'tasks.json';

    public function all(): array
    {
        return $this->withLock(fn () => $this->read());
    }

    public function add(string $description): int
    {
        return $this->withLock(function () use ($description): int {
            $tasks = $this->read();
            $id = $tasks === [] ? 1 : max(array_column($tasks, 'id')) + 1;
            $now = gmdate('c');

            $tasks[] = [
                'id' => $id,
                'description' => $description,
                'status' => 'todo',
                'createdAt' => $now,
                'updatedAt' => $now,
            ];

            $this->write($tasks);

            return $id;
        });
    }

    private function read(): array
    {
        if (!file_exists(self::FILE)) {
            $this->write([]);

            return [];
        }

        $json = @file_get_contents(self::FILE);

        if ($json === false) {
            throw new RuntimeException('Could not read tasks.json.');
        }

        try {
            $tasks = json_decode($json, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('tasks.json contains invalid JSON.', 0, $exception);
        }

        if (!is_array($tasks)) {
            throw new RuntimeException('tasks.json must contain a JSON array.');
        }

        foreach ($tasks as $task) {
            if (!is_int($task->id ?? null)
                || !is_string($task->description ?? null)
                || !is_string($task->status ?? null)) {
                throw new RuntimeException('tasks.json contains an invalid task.');
            }
        }

        return array_map(static fn (object $task): array => (array) $task, $tasks);
    }

    private function write(array $tasks): void
    {
        $json = json_encode($tasks, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        $temp = @tempnam('.', 'tasks-');

        if ($temp === false) {
            throw new RuntimeException('Could not create a temporary task file.');
        }

        try {
            if (@file_put_contents($temp, $json) !== strlen($json)
                || !@rename($temp, self::FILE)) {
                throw new RuntimeException('Could not save tasks.json.');
            }
        } finally {
            if (is_file($temp)) {
                @unlink($temp);
            }
        }
    }

    private function withLock(Closure $operation): mixed
    {
        $lock = @fopen(self::FILE . '.lock', 'c');

        if ($lock === false) {
            throw new RuntimeException('Could not open the task lock file.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new RuntimeException('Could not lock the task file.');
            }

            return $operation();
        } finally {
            fclose($lock);
        }
    }
}
