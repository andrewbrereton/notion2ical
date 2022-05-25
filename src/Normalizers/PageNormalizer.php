<?php

declare(strict_types=1);

namespace App\Normalizers;

use App\Models\Page;
use DateTime;

class PageNormalizer
{
    public static function JsonToObject(array $data): Page
    {
        $id = $data['id'];

        $createdAt = new DateTime($data['created_time']);

        $updatedAt = new DateTime($data['last_edited_time']);

        $startedAt = $data['properties']['When']['date']['start'] ?? null;
        $startedAt = $startedAt ? new DateTime($startedAt): null;

        $tags = implode(' ', array_map(function(array $tag) {
            return '#' . $tag['name'] ?? '';
        }, $data['properties']['Tags']['multi_select'] ?? []));

        $status = $data['properties']['Status']['select']['name'] ?? '';

        $title = implode('', array_map(function(array $title) {
            return $title['plain_text'] ?? '';
        }, $data['properties']['Title']['title'] ?? []));

        $googleCalendarEventId = $data['properties']['Google Calendar Event ID']['rich_text'][0]['plain_text'] ?? '';

        return new Page(
            $id,
            $createdAt,
            $updatedAt,
            $startedAt,
            $tags,
            $status,
            $title,
            $googleCalendarEventId
        );
    }
}