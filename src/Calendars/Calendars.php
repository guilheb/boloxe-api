<?php

namespace API\Calendars;

use API\Calendars\Entities\Calendar;
use Exception;
use NoDiscard;
use Uri\Rfc3986\Uri;

/**
 * Subscribed calendars are declared only in the environment: every CAL_<ID>_URL is one calendar,
 * with optional CAL_<ID>_NAME and CAL_<ID>_COLOR, sorted by ID (Docker doesn't keep the order of the file).
 */
final class Calendars
{
    // Used in order for calendars without a CAL_<ID>_COLOR (Apple Calendar's palette)
    private const array DEFAULT_COLORS = ['#1badf8', '#ffcc00', '#63da38', '#ff2968', '#cc73e1', '#ff9500', '#a2845e'];

    private const int HTTP_TIMEOUT = 15;

    /** @var Calendar[] */
    private(set) array $all = [];

    public function __construct()
    {
        // CAL_<ID>_URL variables that are set → <ID>
        $keys = array_keys(array_filter($_SERVER))
            |> (fn(array $names) => preg_grep('/^CAL_[A-Z0-9_]+_URL$/', $names))
            |> (fn(array $names) => array_map(fn(string $name) => substr($name, 4, -4), $names));

        sort($keys, SORT_STRING);

        foreach ($keys as $i => $key) {
            $this->all[] = new Calendar(
                id: strtolower($key),
                name: ($_SERVER["CAL_{$key}_NAME"] ?? '') ?: null,
                color: ($_SERVER["CAL_{$key}_COLOR"] ?? '') ?: self::DEFAULT_COLORS[$i % count(self::DEFAULT_COLORS)],
                url: $_SERVER["CAL_{$key}_URL"],
            );
        }
    }

    public function get(string $id): ?Calendar
    {
        return array_find($this->all,fn(Calendar $calendar) => $calendar->id === $id);
    }

    /**
     * Downloads the calendar's ICS feed. The URL is kept out of the exception messages: it is private.
     *
     * @throws Exception
     */
    #[NoDiscard]
    public function fetch(Calendar $calendar): string
    {
        $uri = new Uri($calendar->url);

        // webcal:// links become https://
        if (in_array(strtolower($uri->getScheme() ?? ''), ['webcal', 'webcals'], true)) {
            $uri = $uri->withScheme('https');
        }

        $curl = curl_init($uri->toString());
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
        ]);

        $ics = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        if ($ics === false) {
            throw new Exception('HTTP request failed: ' . curl_error($curl));
        }
        elseif ($status !== 200) {
            throw new Exception("HTTP $status");
        }
        elseif (!str_starts_with(ltrim($ics), 'BEGIN:VCALENDAR')) {
            throw new Exception('Response is not an iCalendar feed');
        }

        return $ics;
    }
}
