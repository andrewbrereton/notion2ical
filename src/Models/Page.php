<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use Stringable;

class Page implements Stringable
{
    public string $statusEmoji;
    public string $title;

    private static string $iCalEventTemplate = <<<TEMPLATE
BEGIN:VEVENT\r
UID:{id}\r
DTSTAMP:{dateTimeStamp}\r
DTSTART:{dateStart}\r
SUMMARY:{title}\r
END:VEVENT\r
TEMPLATE;

    public function __construct(
        public string $id,
        public DateTime $createdAt,
        public DateTime $updatedAt,
        public ?DateTime $startedAt,
        public string $tags,
        public string $status,
        string $title,
        public string $googleCalendarEventId
    )
    {
        $this->statusEmoji = match (strtolower($this->status)) {
            'done' => '✅',
            'not doing' => '❌',
            default => '🔲',
        };

        $this->title = $this->statusEmoji . ' ' . $title . ' ' . $this->tags;
    }

    public function __toString(): string
    {
        $iCal = '';

        if ($this->startedAt) {
            #TODO Work out how to make events all day
            $iCal = str_replace('{id}', $this->id, self::$iCalEventTemplate);
            $iCal = str_replace('{dateTimeStamp}', $this->createdAt->format('Ymd\THis'), $iCal);
            $iCal = str_replace('{dateStart}', $this->startedAt->format('Ymd'), $iCal);
//            $iCal = str_replace('{dateTimeEnd}', $this->startedAt->format('Ymd\THis'), $iCal);
            $iCal = str_replace('{title}', $this->title, $iCal);
        }

        return $iCal;
    }
}