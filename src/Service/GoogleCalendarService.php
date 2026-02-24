<?php

namespace App\Service;

use App\Entity\Hackathon;

class GoogleCalendarService
{
    public function generateUrl(Hackathon $hackathon): string
    {
        $baseUrl = "https://www.google.com/calendar/render?action=TEMPLATE";
        $title = urlencode($hackathon->getTitle());

        $start = $hackathon->getStartAt()->format('Ymd\THis\Z');
        $end = $hackathon->getEndAt()->format('Ymd\THis\Z');

        $details = urlencode("Theme: " . $hackathon->getTheme() . "\n\n" . $hackathon->getDescription());
        $location = urlencode($hackathon->getLocation());

        return "{$baseUrl}&text={$title}&dates={$start}/{$end}&details={$details}&location={$location}";
    }
}
