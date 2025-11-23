<?php
// Convert date string to short day format: Mon, Tue, etc.
function shortDay($dateStr) {
    return date('D', strtotime($dateStr));
}

// Convert schedule_days string to array: "Mon-Fri" => ['Mon','Tue','Wed','Thu','Fri']
function expandScheduleDays($sched) {
    $sched = trim($sched);
    if ($sched === '') return [];

    $sched = str_replace([' ', ';'], ['', ','], $sched);

    $daysOrder = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $map = [
        'monday'=>'Mon','mon'=>'Mon',
        'tuesday'=>'Tue','tue'=>'Tue','tues'=>'Tue',
        'wednesday'=>'Wed','wed'=>'Wed',
        'thursday'=>'Thu','thu'=>'Thu','thurs'=>'Thu',
        'friday'=>'Fri','fri'=>'Fri',
        'saturday'=>'Sat','sat'=>'Sat',
        'sunday'=>'Sun','sun'=>'Sun'
    ];

    // handle range like Mon-Fri
    if (strpos($sched,'-') !== false) {
        list($start,$end) = explode('-', $sched, 2);
        $sShort = $map[strtolower(trim($start))] ?? ucfirst(substr($start,0,3));
        $eShort = $map[strtolower(trim($end))] ?? ucfirst(substr($end,0,3));

        $startIndex = array_search($sShort,$daysOrder);
        $endIndex = array_search($eShort,$daysOrder);

        if ($startIndex !== false && $endIndex !== false) {
            if ($startIndex <= $endIndex) return array_slice($daysOrder,$startIndex,$endIndex-$startIndex+1);
            return array_merge(array_slice($daysOrder,$startIndex), array_slice($daysOrder,0,$endIndex+1));
        }
    }

    // comma separated
    $parts = explode(',',$sched);
    $out = [];
    foreach($parts as $p){
        $p = strtolower(trim($p));
        if($p==='') continue;
        $out[] = $map[$p] ?? ucfirst(substr($p,0,3));
    }

    // dedupe and order
    $ordered=[];
    foreach($daysOrder as $d){
        if(in_array($d,$out) && !in_array($d,$ordered)) $ordered[]=$d;
    }
    return $ordered;
}

// Get default dentist for a location and date
function getDefaultDentist($conn, $location, $date){
    $day = shortDay($date);
    $stmt = $conn->prepare("SELECT id, name, schedule_days FROM dentists WHERE TRIM(location)=? AND is_active=1 ORDER BY id ASC");
    $stmt->bind_param("s",$location);
    $stmt->execute();
    $res = $stmt->get_result();
    while($d = $res->fetch_assoc()){
        $days = expandScheduleDays($d['schedule_days']);
        if(in_array($day,$days)) return $d;
    }
    return null;
}
?>
