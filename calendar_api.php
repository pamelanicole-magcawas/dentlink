<?php
// calendar_api.php
$API_BASE = "https://v1.nocodeapi.com/sgdentalclinic/calendar/BjRLPQRVQhlpwKXa";

function call_calendar_api($endpoint, $method = 'GET', $data = null) {
    global $API_BASE;
    $url = "$API_BASE/$endpoint";
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => ($method === 'GET' ? null : json_encode($data))
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) return ['error' => $error];
    return json_decode($response, true);
}

// =================== Helper functions ===================
function list_calendars() {
    return call_calendar_api("calendarList");
}

function list_events() {
    return call_calendar_api("listEvents");
}

function get_event($eventId) {
    return call_calendar_api("event?eventId=$eventId");
}

function create_event($eventData) {
    return call_calendar_api("event", "POST", $eventData);
}

function delete_event($eventId) {
    return call_calendar_api("event?eventId=$eventId", "DELETE");
}
