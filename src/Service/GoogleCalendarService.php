<?php

namespace App\Service;

use App\Entity\Hackathon;

class GoogleCalendarService
{
    public function generateUrl(Hackathon $hackathon): string
    {
        $baseUrl = "https://www.google.com/calendar/render?action=TEMPLATE";
        $title = urlencode((string) $hackathon->getTitle());

        $startAt = $hackathon->getStartAt();
        $endAt = $hackathon->getEndAt();
        if ($startAt === null || $endAt === null) {
            return $baseUrl . '&text=' . $title;
        }

        $start = $startAt->format('Ymd\THis\Z');
        $end = $endAt->format('Ymd\THis\Z');

        $details = urlencode("Theme: " . (string) $hackathon->getTheme() . "\n\n" . (string) $hackathon->getDescription());
        $location = urlencode((string) $hackathon->getLocation());

        return "{$baseUrl}&text={$title}&dates={$start}/{$end}&details={$details}&location={$location}";
    }
}
