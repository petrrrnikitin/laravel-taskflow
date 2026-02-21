<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Invitation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #4f46e5; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 32px; color: #374151; }
        .body p { margin: 0 0 16px; line-height: 1.6; }
        .project-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
        .project-card .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        .project-card .value { font-size: 15px; color: #111827; font-weight: 600; }
        .footer { padding: 20px 32px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>TaskFlow</h1>
    </div>
    <div class="body">
        <p>Hi <strong>{{ $invitee->name }}</strong>,</p>
        <p>You have been added as a member to the following project:</p>

        <div class="project-card">
            <div class="label">Project</div>
            <div class="value">{{ $project->name }}</div>

            @if($project->description)
                <div style="margin-top: 12px;">
                    <div class="label">Description</div>
                    <div style="font-size:14px; color:#374151;">{{ $project->description }}</div>
                </div>
            @endif
        </div>

        <p>Log in to TaskFlow to start collaborating.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} TaskFlow. This is an automated notification.
    </div>
</div>
</body>
</html>