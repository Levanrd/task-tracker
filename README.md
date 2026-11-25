# Task Tracker CLI

Task Tracker CLI is a Laravel 13 command-line app for adding and listing tasks. It stores tasks locally in a JSON file.

## Getting started

Use PHP 8.5 and Composer. In PowerShell or Bash, run these commands for a fresh checkout.

```sh
git clone https://github.com/Levanrd/task-tracker.git
cd task-tracker
composer install
cp .env.example .env
php artisan key:generate --no-interaction
```

## Usage

Run these commands from the project directory. Put descriptions that contain spaces in quotes.

```sh
php artisan task:add "Buy groceries"
php artisan task:list
php artisan task:list todo
```

A new task starts as `todo`. The add command prints its ID, and the list command displays each matching task's ID, status, and description.

| Command | Result |
| --- | --- |
| `php artisan task:add "Buy groceries"` | Add a task. |
| `php artisan task:list` | List all tasks. |
| `php artisan task:list todo` | List tasks with the `todo` status. |
| `php artisan task:list in-progress` | List tasks with the `in-progress` status. |
| `php artisan task:list done` | List tasks with the `done` status. |
| `php artisan task:list not-done` | List both `todo` and `in-progress` tasks. |

Updating, deleting, and changing a task's status are planned features.

## Task storage

The CLI creates `tasks.json` on first use in the directory where you run the command. Each task stores its ID, description, status, and UTC creation and update timestamps. Git ignores both `tasks.json` and its companion lock file.
