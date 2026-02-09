<x-mail::message>
# Match Reminder

Your match is starting in **{{ $timeUntil }}**!

## Match Details

- **Tournament:** {{ $tournamentName }}
- **Match:** Round {{ $round }}, Match {{ $matchNumber }}
- **Teams:** {{ $team1 }} vs {{ $team2 }}
- **Scheduled:** {{ $scheduledAt->format('F j, Y \a\t g:i A') }}

## Action Required

Please check in for your match before the scheduled time. Failure to check in may result in a forfeit.

<x-mail::button :url="$checkInUrl" color="success">
Check In Now
</x-mail::button>

<x-mail::button :url="$matchUrl">
View Match Details
</x-mail::button>

Good luck and have fun!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
