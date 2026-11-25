<?php

$glob = glob('.scratch-uma/sqlite/*.sqlite');
echo 'scratch dbs: '.count($glob)."\n";
foreach ($glob as $f) {
    $d = new PDO('sqlite:'.$f);
    $bad = false;
    $umamusume = false;
    foreach ($d->query("select name from sqlite_master where type='table'") as $r) {
        $t = $r['name'];
        $fk = $d->query("PRAGMA foreign_key_list($t)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fk as $x) {
            if ($x['table'] === 'umamusumes') {
                $bad = true;
            }
        }
        if ($t === 'umamusume') {
            $umamusume = true;
        }
    }
    $hasRoster = (bool) $d->query("select count(*) from sqlite_master where type='table' and name='roster_members'")->fetchColumn();
    $perms = substr(sprintf('%o', fileperms($f)), -4);
    $writable = is_writable($f);
    echo basename($f)." perms=$perms writable=".($writable ? 'yes' : 'NO').' umamusume_table='.($umamusume ? 'yes' : 'no').' roster_table='.($hasRoster ? 'yes' : 'no').' broken_umamusumes_fk='.($bad ? 'YES' : 'no')."\n";
}
