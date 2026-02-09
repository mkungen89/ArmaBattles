<x-mail::message>
# Team Invitation

Hi there!

**{{ $captainName }}** has invited you to join **{{ $teamName }}** {{ $teamTag ? "({$teamTag})" : '' }}!

## Team Details

- **Team Name:** {{ $teamName }}
- **Team Tag:** {{ $teamTag ?? 'None' }}
- **Captain:** {{ $captainName }}

## What's Next?

Accept this invitation to become part of the team and participate in tournaments together!

<x-mail::button :url="$acceptUrl" color="success">
Accept Invitation
</x-mail::button>

<x-mail::button :url="$declineUrl" color="error">
Decline Invitation
</x-mail::button>

**Note:** This invitation expires on {{ $expiresAt->format('F j, Y \a\t g:i A') }}.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
