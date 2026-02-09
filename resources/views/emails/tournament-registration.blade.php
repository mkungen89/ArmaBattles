<x-mail::message>
# Tournament Registration Confirmed

Your team **{{ $teamName }}** has been registered for **{{ $tournamentName }}**!

## Tournament Details

- **Format:** {{ $format }}
- **Start Date:** {{ $startDate->format('F j, Y \a\t g:i A') }}
- **Teams Registered:** {{ $currentTeams }} / {{ $maxTeams }}
- **Status:** {{ ucfirst($registrationStatus) }}

## What's Next?

@if($registrationStatus === 'pending')
Your registration is pending approval. You'll receive another email once approved.
@elseif($registrationStatus === 'approved')
Your team is confirmed! Matches will be scheduled soon. Keep an eye on your email for match reminders.
@endif

<x-mail::button :url="$tournamentUrl" color="success">
View Tournament
</x-mail::button>

<x-mail::button :url="$rulesUrl">
Read Rules
</x-mail::button>

## Important Reminders

- Check in for your matches on time to avoid forfeits
- Review the tournament rules before your first match
- Stay connected for schedule updates

Good luck in the tournament!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
