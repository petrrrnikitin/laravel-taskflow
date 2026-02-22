<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1   { font-size: 20px; margin-bottom: 4px; }
        h2   { font-size: 14px; margin-top: 24px; margin-bottom: 8px; color: #444; }
        .meta { color: #666; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th    { background: #f0f0f0; text-align: left; padding: 6px 8px; font-size: 11px; }
        td    { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .status-todo        { background: #e0e0e0; color: #333; }
        .status-in_progress { background: #dbeafe; color: #1d4ed8; }
        .status-done        { background: #dcfce7; color: #15803d; }
        .priority-low    { background: #f0fdf4; color: #16a34a; }
        .priority-medium { background: #fefce8; color: #ca8a04; }
        .priority-high   { background: #fef2f2; color: #dc2626; }
    </style>
</head>
<body>
    <h1>{{ $project->name }}</h1>
    <div class="meta">
        Status: {{ ucfirst($project->status->value) }}
        &nbsp;·&nbsp;
        Generated: {{ now()->toDateTimeString() }}
    </div>

    @if($project->description)
        <p>{{ $project->description }}</p>
    @endif

    <h2>Tasks ({{ $project->tasks->count() }})</h2>

    @if($project->tasks->isEmpty())
        <p>No tasks found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assignee</th>
                    <th>Due date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->tasks as $task)
                    <tr>
                        <td>{{ $task->id }}</td>
                        <td>{{ $task->title }}</td>
                        <td>
                            <span class="badge status-{{ $task->status->value }}">
                                {{ str_replace('_', ' ', ucfirst($task->status->value)) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge priority-{{ $task->priority->value }}">
                                {{ ucfirst($task->priority->value) }}
                            </span>
                        </td>
                        <td>{{ $task->assignee?->name ?? '—' }}</td>
                        <td>{{ $task->due_date?->toDateString() ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>