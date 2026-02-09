<x-mail::message>
# 🏆 Achievement Unlocked!

Congratulations, **{{ $userName }}**!

You've unlocked a new achievement:

<x-mail::panel>
## {{ $achievementIcon }} {{ $achievementName }}

{{ $achievementDescription }}

@if($isRare)
**This is a RARE achievement!** Only {{ $rarityPercentage }}% of players have unlocked it.
@else
{{ $rarityPercentage }}% of players have unlocked this achievement.
@endif
</x-mail::panel>

## View Your Achievements

Check out all your unlocked achievements and see what's next!

<x-mail::button :url="$achievementsUrl" color="success">
View All Achievements
</x-mail::button>

<x-mail::button :url="$profileUrl">
Go to Profile
</x-mail::button>

Keep up the great work!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
