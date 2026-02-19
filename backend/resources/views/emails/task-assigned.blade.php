<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assigned</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #4f46e5; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 32px; color: #374151; }
        .body p { margin: 0 0 16px; line-height: 1.6; }
        .task-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .task-card .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        .task-card .value { font-size: 15px; color: #111827; font-weight: 600; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-high { background: #fee2e2; color: #dc2626; }
        .badge-medium { background: #fef3c7; color: #d97706; }
        .badge-low { background: #dcfce7; color: #16a34a; }
        .footer { padding: 20px 32px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>TaskFlow</h1>
    </div>
    <div class="body">
        <p>Hi <strong>{{ $assignee->name }}</strong>,</p>
        <p>You have been assigned to a new task.</p>

        <div class="task-card">
            <div class="label">Task</div>
            <div class="value">{{ $task->title }}</div>

            @if($task->description)
                <div style="margin-top: 12px;">
                    <div class="label">Description</div>
                    <div style="font-size:14px; color:#374151;">{{ $task->description }}</div>
                </div>
            @endif

            <div style="margin-top: 12px; display: flex; gap: 24px;">
                <div>
                    <div class="label">Priority</div>
                    <span class="badge badge-{{ $task->priority->value }}">{{ ucfirst($task->priority->value) }}</span>
                </div>
                @if($task->due_date)
                    <div>
                        <div class="label">Due date</div>
                        <div class="value">{{ $task->due_date->format('M d, Y') }}</div>
                    </div>
                @endif
            </div>
        </div>

        <p>Log in to TaskFlow to view the full details and get started.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} TaskFlow. This is an automated notification.
    </div>
</div>
</body>
</html>