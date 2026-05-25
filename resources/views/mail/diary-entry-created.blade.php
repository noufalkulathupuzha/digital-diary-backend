<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You have added an entry</title>
</head>
<body style="margin:0;padding:0;background-color:#f7f6f3;font-family:Georgia,'Times New Roman',serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f6f3;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border:1px solid #e8e6e1;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:32px 28px;">
                            <p style="margin:0 0 8px;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:#9b9a97;">
                                Digital Diary
                            </p>
                            <h1 style="margin:0 0 16px;font-size:24px;font-weight:normal;color:#2f2e2b;">
                                You have added an entry
                            </h1>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#6f6e6a;">
                                Your journal entry <strong style="color:#2f2e2b;">{{ $entry->title }}</strong>
                                was saved on {{ $entry->entry_date->format('F j, Y') }}.
                            </p>
                            @if($entry->mood)
                                <p style="margin:0 0 20px;font-size:14px;color:#6f6e6a;">
                                    Mood: <span style="color:#3d6b5e;">{{ ucfirst($entry->mood) }}</span>
                                </p>
                            @endif
                            <a href="{{ rtrim(config('app.frontend_url'), '/') }}/dashboard/entries/{{ $entry->id }}"
                               style="display:inline-block;padding:12px 20px;background:#3d6b5e;color:#ffffff;text-decoration:none;border-radius:10px;font-size:14px;">
                                View entry
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0;font-size:12px;color:#9b9a97;">
                    You received this email because you created a diary entry on {{ config('app.name') }}.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
