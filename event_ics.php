<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) {
    http_response_code(400);
    exit('Invalid event.');
}
$statement = db()->prepare('SELECT id, title, event_date, location, details FROM events WHERE id = ? LIMIT 1');
$statement->execute([$eventId]);
$event = $statement->fetch();
if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}
$escapeIcs = static function ($value) {
    return str_replace(["\\", ";", ",", "\r\n", "\r", "\n"], ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"], (string) $value);
};
$start = date('Ymd', strtotime($event['event_date'])) . 'T090000';
$end = date('Ymd', strtotime($event['event_date'])) . 'T160000';
$filename = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower((string) $event['title'])) ?: 'brd-event';
$calendar = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//BRD Tanzania//Community Calendar//EN\r\nBEGIN:VEVENT\r\nUID:brd-event-{$event['id']}@brd.local\r\nDTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\nDTSTART;TZID=Africa/Dar_es_Salaam:{$start}\r\nDTEND;TZID=Africa/Dar_es_Salaam:{$end}\r\nSUMMARY:" . $escapeIcs($event['title']) . "\r\nLOCATION:" . $escapeIcs($event['location']) . "\r\nDESCRIPTION:" . $escapeIcs($event['details']) . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '.ics"');
echo $calendar;
