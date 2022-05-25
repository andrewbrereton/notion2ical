<?php

declare(strict_types=1);

namespace App\Models;

use Countable;
use Iterator;
use Stringable;

class PageCollection implements Countable, Stringable, Iterator
{
    private array $pages;
    private int $position = 0;

    private static string $iCalCalendarTemplate = <<<TEMPLATE
BEGIN:VCALENDAR\r
VERSION:2.0\r
PRODID:-//Andrew Brereton//notion2ical v1.0//EN\r
X-WR-CALNAME:Notion Calendar\r
NAME:Notion Calendar\r
CALSCALE:GREGORIAN\r
{events}\r
END:VCALENDAR
TEMPLATE;

    public function __construct()
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->pages);
    }

    public function __toString(): string
    {
        // Call each array elements __toString() method
        $pagesString = implode($this->pages);

        $iCal = str_replace('{events}', $pagesString, self::$iCalCalendarTemplate);

        $iCal = $this->pretty($iCal);

        return $iCal;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): Page
    {
        return $this->pages[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->array[$this->position]);
    }

    public function add(Page $page)
    {
        $this->pages[] = $page;
    }

    public function pretty(string $iCal): string
    {
        // Replace two or more /r or /n or /r/n with a single CRLF
        $iCal = preg_replace('/\R{2,}/', "\r\n", $iCal);

        // Ensure all line endings are CRLF. Have to do 'BSR_ANYCRLF' so we don't break emojis
        $iCal = preg_replace('~(*BSR_ANYCRLF)\R~', "\r\n", $iCal);

        // Line length should not be longer than 75 characters (https://icalendar.org/iCalendar-RFC-5545/3-1-content-lines.html)
        #TODO I can't be bother implementing this *should* requirement

        // Ensure we have UTF-8
        $iCal = mb_convert_encoding($iCal, 'UTF-8');

        return $iCal;
    }
}