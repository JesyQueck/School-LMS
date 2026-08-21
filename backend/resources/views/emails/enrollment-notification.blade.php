@php
    $isStudent = $user->role === 'student';
    $isParent = $user->role === 'parent';
    $isTeacher = $user->role === 'teacher';

    $greeting = $isParent ? 'Dear Parent' : ($isTeacher ? 'Dear Teacher' : 'Dear Student');
    $roleLabel = $isStudent ? 'student' : ($isParent ? 'parent' : 'teacher');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $schoolName }} — Account Created</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f7fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">

        <tr>
            <td style="background: linear-gradient(135deg, #3b82f6 0%, #7c3aed 100%); padding: 40px; text-align: center;">
                <h1 style="color: #ffffff; font-size: 28px; margin: 0; font-weight: 700;">{{ $schoolName }}</h1>
                <p style="color: #e0e7ff; font-size: 14px; margin: 8px 0 0; opacity: 0.9;">Education Portal</p>
            </td>
        </tr>

        <tr>
            <td style="padding: 40px;">
                <h2 style="margin: 0 0 16px; color: #111827; font-size: 20px;">Welcome — Your Account Has Been Created</h2>
                <p style="font-size: 16px; line-height: 1.6; color: #374151; margin: 0 0 24px;">
                    {{ $greeting }}, your {{ $roleLabel }} account for {{ $schoolName }} has been successfully created.
                </p>

                @if($relatedStudent)
                    <p style="font-size: 16px; line-height: 1.6; color: #374151; margin: 0 0 24px;">
                        This account is associated with your child: <strong>{{ $relatedStudent }}</strong>.
                    </p>
                @endif

                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; padding: 24px; margin-bottom: 24px;">
                    <tr>
                        <td>
                            <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin: 0 0 4px; font-weight: 600;">Your Login Email</p>
                            <p style="font-size: 16px; font-family: 'Courier New', monospace; color: #111827; word-break: break-all;">{{ $user->email }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 16px;">
                            <p style="font-size: 12px; text-transform: uppercase; letter-tracking: 0.5px; color: #6b7280; margin: 0 0 4px; font-weight: 600;">Temporary Password</p>
                            <p style="font-size: 20px; font-family: 'Courier New', monospace; color: #dc2626; font-weight: 700; letter-spacing: 2px;">{{ $temporaryPassword }}</p>
                        </td>
                    </tr>
                </table>

                <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px; padding: 16px 20px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 14px; color: #1e40af; line-height: 1.5;">
                        <strong style="display: block; margin-bottom: 4px;">Important:</strong>
                        You must change your password on first login. After logging in, you will be prompted to create a new password.
                    </p>
                </div>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                    <tr>
                        <td width="24"><img src="https://img.icons8.com/ios-filled/24/374159/navigate.png" alt="" width="24" height="24" style="display: block;"></td>
                        <td style="padding-left: 12px;">
                            <p style="margin: 0; font-size: 14px; color: #374151;">Go to <a href="{{ config('app.url') }}/login" style="color: #3b82f6; font-weight: 600;">the login portal</a> and sign in using the credentials above.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 12px;"><img src="https://img.icons8.com/ios-filled/24/374159/change-request.png" alt="" width="24" height="24" style="display: block;"></td>
                        <td style="padding-left: 12px; padding-top: 12px;">
                            <p style="margin: 0; font-size: 14px; color: #374151;">Create a new password that you will remember. Make it unique and secure.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 12px;"><img src="https://img.icons8.com/ios-filled/24/374159/lock.png" alt="" width="24" height="24" style="display: block;"></td>
                        <td style="padding-left: 12px; padding-top: 12px;">
                            <p style="margin: 0; font-size: 14px; color: #374151;">Your account will be fully secured and ready to use.</p>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 14px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 20px; margin: 0;">
                    If you did not expect this account, please contact the school administration at {{ config('school.email', 'info@school.com') }}.
                </p>
            </td>
        </tr>

        <tr>
            <td style="background-color: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                    &copy; {{ date('Y') }} {{ $schoolName }}. All rights reserved.
                </p>
                <p style="font-size: 12px; color: #9ca3af; margin: 4px 0 0;">
                    {{ config('app.url') }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
