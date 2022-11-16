<?php

/**
 * EventHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2020 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/**
 * Helper trait to create timer and events.
 */
trait EventHelper
{
    /**
     * Update interval for a cyclic timer.
     *
     * @param string $ident  Name and ident of the timer.
     * @param int    $hour   Start hour.
     * @param int    $minute Start minute.
     * @param int    $second Start second.
     */
    protected function UpdateTimerInterval($ident, $hour, $minute, $second)
    {
        $now = new DateTime();
        $target = new DateTime();
        $target->modify('+1 day');
        $target->setTime($hour, $minute, $second);
        $diff = $target->getTimestamp() - $now->getTimestamp();
        $interval = $diff * 1000;
        $this->SetTimerInterval($ident, $interval);
    }

    /**
     * Creates a weekly schedule.
     *
     * @param int    $id     Parent ID
     * @param string $name   Schedule name
     * @param string $ident  Internal identifier
     * @param array  $datas Array with switch states
     * @param int    $pos    Position (sort order)
     * @return array ID of the existing schedule or of the new created schedule.
     */
    protected function CreateWeeklySchedule($id, $name, $ident, $datas, $pos = 0)
    {
        $eid = @IPS_GetObjectIDByIdent($ident, $id);
        if ($eid === false) {
            $eid = IPS_CreateEvent(EVENTTYPE_SCHEDULE);
            IPS_SetName($eid, $name);
            IPS_SetIdent($eid, $ident);
            IPS_SetParent($eid, $id);
            IPS_SetPosition($eid, $pos);
            foreach ($datas as $key => $value) {
                IPS_SetEventScheduleAction($eid, $key, $value[0], $value[1], $value[2]);
            }
            // Mo - So (1 + 2 + 4 + 8 + 16 + 32 + 64) = 127; Mo - Fr (1 + 2 + 4 + 8 + 16) = 31; Sa + So (32 + 64) = 96
            IPS_SetEventScheduleGroup($eid, 0, 127);
            IPS_SetEventScheduleGroupPoint($eid, 0, 1, 0, 0, 0, 1);
            IPS_SetEventActive($eid, true);
        }
        return $eid;
    }

    /**
     * Reads out the status of a desired weekly schedule event.
     *
     * @param int $id Weekly schedule ID
     * @param int $time Query time as system time
     * @param bool $checkonly Check only slot
     * @return array [
     *   'ActionID' => 4 ==> Active state at the time of the query
     *   'ActionName' => NAME ==> Status description at the time of the query
     *   'CheckSysTime' => 1423986592 ==> Time for which the check was started
     *   'CheckTime' => 15.02.2015 08:49:52 ==> Formatted check time
     *   'StartSysTime' => 1423861200 ==> switching point where the ACTIVE STATE became active
     *   'StartTime' => 13.02.2015 22:00:00 ==> Formatted starting point
     *   'EndSysTime' => 1424034000 ==> switching point where the ACTIVE STATE is left
     *   'EndTime' => 2/15/2015 10:00 PM ==> Formatted endpoint
     *   'Period' => 172800 ==> Duration of the active state in seconds
     *   'PeriodHours' => 48 ==> Formatted time HOURS
     *   'PeriodMinutes' => 0 ==> Formatted duration MINUTES
     *   'PeriodSeconds' => 0 ==> Formatted duration SECONDS
     *   'PreviousActionID' => 2 ==> State of the BEFORE the current state
     *   'PreviousActionName' => 2 middle class ==> state description of previous state
     *   'NextActionID' => 3 ==> Status that the current status is removed from
     *   'NextActionName' => 3-night shift ==> State description of future state
     *   'WeekPlanID' => 15405 ==> ID of the weekly schedule
     *   'WeekPlanName' => SHIFT PLAN TEST ==> Name of the weekly plan
     *   'WeekPlanActiv' => 1 ==> State whether the weekly schedule is active or not
     * ]
     */
    protected function GetWeeklyScheduleInfo($id, $time = null, $checkonly = false)
    {
        if ($time == null) {
            $time = time();
        }

        $data = [];
        $data['ActionID'] = 0;
        $data['ActionName'] = '';
        $data['CheckSysTime'] = $time;
        $data['CheckTime'] = '01.01.1970 00:00:00';
        $data['StartSysTime'] = $time - 86400 * 7;
        $data['StartTime'] = '01.01.1970 00:00:00';
        $data['EndSysTime'] = $time + 86400 * 7;
        $data['EndTime'] = '01.01.1970 00:00:00';
        $data['Periode'] = 0;
        $data['PeriodeHours'] = 0;
        $data['PeriodeMinutes'] = 0;
        $data['PeriodeSeconds'] = 0;
        $data['PreviousActionID'] = 0;
        $data['PreviousActionName'] = '';
        $data['NextActionID'] = 0;
        $data['NextActionName'] = '';
        $data['WeekPlanID'] = $id;
        $data['WeekPlanName'] = IPS_GetName($id);
        $data['WeekPlanActiv'] = 0;

        $eid = IPS_GetEvent($id);
        if ($eid['EventType'] != EVENTTYPE_SCHEDULE) {
            $this->SendDebug('GetWeeklyScheduleInfo', 'Bei der ID= ' . $id . ' handelt es sich um keinen Wochenplan !');
            return $data;
        }

        if ($eid['EventActive'] == 1) {
            $data['WeekPlanActiv'] = 1;
        }

        $startPointFound = false;
        $endPointFound = false;
        $dayEventFound = false;
        $dayFound = false;

        //Durch alle Gruppen gehen
        foreach ($eid['ScheduleGroups'] as $g) {
            //pruefen ob Gruppe fuer Zeitpunkt zustaendig
            if (($g['Days'] & pow(2, date('N', $time) - 1)) > 0) {
                $dayFound = true;
                $startPointFound = false;
                $actualSlotFound = false;
                $endPointFound = false;
                $data['StartSysTime'] = mktime(0, 0, 0, intval(date('m', $time)), intval(date('d', $time)), intval(date('Y', $time)));
                $searchTimeActDay = date('H', $time) * 3600 + date('i', $time) * 60 + date('s', $time);
                //Aktuellen Schaltpunkt suchen --> Wir nutzen die Eigenschaft, dass die Schaltpunkte immer aufsteigend sortiert sind.
                foreach ($g['Points'] as $p) {
                    $StartTimeActDaySlot = $p['Start']['Hour'] * 3600 + $p['Start']['Minute'] * 60 + $p['Start']['Second'];
                    $dayEventFound = true;

                    if ($searchTimeActDay >= $StartTimeActDaySlot) {
                        if ($actualSlotFound == false) {
                            $actualSlotFound = true;
                            $data['ActionID'] = $p['ActionID'];
                            $data['StartSysTime'] = mktime($p['Start']['Hour'], $p['Start']['Minute'], $p['Start']['Second'], intval(date('m', $time)), intval(date('d', $time)), intval(date('Y', $time)));
                        } else {
                            $startPointFound = true;
                            $data['PreviousActionID'] = $data['ActionID'];
                            $data['ActionID'] = $p['ActionID'];
                            $data['StartSysTime'] = mktime($p['Start']['Hour'], $p['Start']['Minute'], $p['Start']['Second'], intval(date('m', $time)), intval(date('d', $time)), intval(date('Y', $time)));
                        }
                    } else {
                        if ($endPointFound == false) {
                            $endPointFound = true;
                            $data['NextActionID'] = $p['ActionID'];
                            $data['EndSysTime'] = mktime($p['Start']['Hour'], $p['Start']['Minute'], $p['Start']['Second'], intval(date('m', $time)), intval(date('d', $time)), intval(date('Y', $time)));
                        } else {
                            break;
                        }
                    }
                }
                break; //Sobald wir unseren Tag gefunden haben, können wir die Schleife abbrechen. Jeder Tag darf nur in genau einer Gruppe sein.
            }
        }

        //wenn kein Tag gefunden wird ==> Tag ist ausgeblendet !!
        if ($dayFound == false) {
            if ($checkonly == false) {
                for ($i = 0; $i <= 7; $i++) {
                    foreach ($eid['ScheduleGroups'] as $g) {
                        //pruefen ob Gruppe fuer Zeitpunkt zustaendig
                        if (($g['Days'] & pow(2, date('N', $time) - 1 - $i)) > 0) {
                            $dayFound = true;
                            break 2;
                        }
                    }
                }
                $data['StartSysTime'] = mktime(00, 00, 00, intval(date('m', $time)), intval(date('d', $time)) + 1 - $i, intval(date('Y', $time)));
            }

            for ($i = 0; $i <= 6; $i++) {
                foreach ($eid['ScheduleGroups'] as $g) {
                    //pruefen ob Gruppe fuer Zeitpunkt zustaendig
                    if (($g['Days'] & pow(2, date('N', $time) - 1 + $i)) > 0) {
                        $dayFound = true;
                        break 2;
                    }
                }
            }

            if ($data['StartSysTime'] <= $time) {
                $data['StartSysTime'] = $time;
            }

            $data['CheckSysTime'] = mktime(00, 00, 00, intval(date('m', $time)), intval(date('d', $time)) - 1 + $i, intval(date('Y', $time)));
            $data['EndSysTime'] = mktime(00, 00, 00, intval(date('m', $time)), intval(date('d', $time)) - 1 + $i, intval(date('Y', $time)));
            $endPointFound = false;
        }

        if ($checkonly == false) {
            //Startpunkt wurde zwar gefunden aber die ActionID ist 0 --> vorigen Schaltpunkt suchen
            if (($startPointFound == true) && ($data['ActionID'] == 0)) {
                do {
                    $prevEvent = $this->GetWeeklyScheduleInfo($id, $data['StartSysTime'] - 1, true);

                    $data['StartSysTime'] = $prevEvent['StartSysTime'];
                    $data['PreviousActionID'] = $prevEvent['ActionID'];

                    if (($data['ActionID'] == 0) && ($prevEvent['ActionID'] != 0)) {
                        $data['StartSysTime'] = $prevEvent['StartSysTime'];
                        $data['ActionID'] = $prevEvent['ActionID'];
                        $data['PreviousActionID'] = $prevEvent['PreviousActionID'];
                        $dayEventFound = true;
                    }
                } while (($data['ActionID'] == 0) && ($prevEvent['StartSysTime'] >= ($data['CheckSysTime'] - 86400 * 7)));

                //Jetzt auch nochmals checken ob sich nicht auch der vorherige zu 0 veraendert hat
                if ($data['PreviousActionID'] == 0) {
                    $checkTime = $data['StartSysTime'];

                    do {
                        $prevEvent = $this->GetWeeklyScheduleInfo($id, $checkTime - 1, true);
                        $checkTime = $prevEvent['StartSysTime'];

                        if (($data['PreviousActionID'] == 0) && ($prevEvent['ActionID'] != 0)) {
                            $data['PreviousActionID'] = $prevEvent['ActionID'];
                            $dayEventFound = true;
                        }
                    } while (($data['PreviousActionID'] == 0) && ($prevEvent['StartSysTime'] >= ($data['CheckSysTime'] - 86400 * 14))); //hier geht auch 7
                }
            }

            //Startpunkt liegt an einen der Vortage !!
            if ($startPointFound == false) {
                do {
                    $prevEvent = $this->GetWeeklyScheduleInfo($id, $data['StartSysTime'] - 1, true);

                    if (($prevEvent['ActionID'] == 0) && ($prevEvent['PreviousActionID'] == 0) && ($prevEvent['NextActionID'] == 0)) {
                        $data['StartSysTime'] = mktime(00, 00, 00, intval(date('m', $prevEvent['StartSysTime'])), intval(date('d', $prevEvent['StartSysTime'])), intval(date('Y', $prevEvent['StartSysTime'])));
                    } elseif (($prevEvent['NextActionID'] == 0) && ($prevEvent['PreviousActionID'] == 0) && ($data['ActionID'] == $prevEvent['ActionID'])) {
                        $data['StartSysTime'] = mktime(00, 00, 00, intval(date('m', $prevEvent['StartSysTime'])), intval(date('d', $prevEvent['StartSysTime'])), intval(date('Y', $prevEvent['StartSysTime'])));
                    } else {
                        $data['StartSysTime'] = $prevEvent['StartSysTime'];
                        $data['PreviousActionID'] = $prevEvent['ActionID'];

                        if (($data['ActionID'] == 0) && ($prevEvent['ActionID'] != 0)) {
                            $data['StartSysTime'] = $prevEvent['EndSysTime']; //??
                            $data['ActionID'] = $prevEvent['ActionID'];
                            $data['PreviousActionID'] = $prevEvent['PreviousActionID'];
                            if ($prevEvent['NextActionID'] == 0) {
                                $data['StartSysTime'] = $prevEvent['StartSysTime'];
                            }

                            $dayEventFound = true;
                        }
                    }
                } while ((($data['ActionID'] == 0) || ($data['ActionID'] == $prevEvent['PreviousActionID'])) && ($prevEvent['StartSysTime'] >= ($data['CheckSysTime'] - 86400 * 14)));

                //Checken ob nicht doch der vorherige Schaltpunkt jetzt 0 ist
                if ($data['PreviousActionID'] == 0) {
                    $checkTime = $data['StartSysTime'];

                    do {
                        $prevEvent = $this->GetWeeklyScheduleInfo($id, $checkTime - 1, true);
                        if (($prevEvent['ActionID'] == 0) && ($prevEvent['PreviousActionID'] == 0) && ($prevEvent['NextActionID'] == 0)) {
                            $checkTime = mktime(00, 00, 00, intval(date('m', $prevEvent['StartSysTime'])), intval(date('d', $prevEvent['StartSysTime'])), intval(date('Y', $prevEvent['StartSysTime'])));
                        } else {
                            $checkTime = $prevEvent['StartSysTime'];
                        }

                        if (($data['PreviousActionID'] == 0) && ($prevEvent['ActionID'] != 0)) {
                            $data['PreviousActionID'] = $prevEvent['ActionID'];
                            $dayEventFound = true;
                        }
                    } while (($data['PreviousActionID'] == 0) && ($prevEvent['StartSysTime'] >= ($data['CheckSysTime'] - 86400 * 14)));
                }
            }

            //Vorheriger Schaltpunkt hat selben Status wie ActionID --> somit vorherigen Schaltpunkt fuer VORGAENGER suchen
            if ($data['ActionID'] == $data['PreviousActionID']) {
                $checkTime = $data['StartSysTime'];

                do {
                    $prevEvent = $this->GetWeeklyScheduleInfo($id, $checkTime - 1, true);

                    if (($prevEvent['ActionID'] == 0) && ($prevEvent['PreviousActionID'] == 0) && ($prevEvent['NextActionID'] == 0)) {
                        $checkTime = mktime(00, 00, 00, intval(date('m', $prevEvent['StartSysTime'])), intval(date('d', $prevEvent['StartSysTime'])), intval(date('Y', $prevEvent['StartSysTime'])));
                    } elseif (($prevEvent['NextActionID'] == 0) && ($prevEvent['PreviousActionID'] == 0)) {
                        $checkTime = mktime(00, 00, 00, intval(date('m', $prevEvent['StartSysTime'])), intval(date('d', $prevEvent['StartSysTime'])), intval(date('Y', $prevEvent['StartSysTime'])));
                    } else {
                        $checkTime = $prevEvent['StartSysTime'];

                        if (($data['PreviousActionID'] != $prevEvent['ActionID']) && ($prevEvent['ActionID'] != 0)) {
                            $data['PreviousActionID'] = $prevEvent['ActionID'];
                            $data['StartSysTime'] = $prevEvent['EndSysTime'];
                            $dayEventFound = true;
                        }
                    }
                } while (($data['PreviousActionID'] == $data['ActionID']) && ($prevEvent['StartSysTime'] >= ($data['CheckSysTime'] - 86400 * 7)));
            }

            //Endpunkt wurde zwar gefunden aber der naechste Schaltpunkt ist 0
            if (($endPointFound == true) && ($data['NextActionID'] == 0)) {
                $checkTime = $data['EndSysTime'];

                do {
                    $nextEvent = $this->GetWeeklyScheduleInfo($id, $checkTime, true);

                    if (($nextEvent['ActionID'] == 0) && ($nextEvent['PreviousActionID'] == 0) && ($nextEvent['NextActionID'] == 0)) {
                        $checkTime = mktime(0, 0, 0, intval(date('m', $nextEvent['StartSysTime'])), intval(date('d', $nextEvent['StartSysTime'])) + 1, intval(date('Y', $nextEvent['StartSysTime'])));
                    } elseif (($nextEvent['NextActionID'] == 0) && ($nextEvent['PreviousActionID'] == 0)) {
                        $checkTime = mktime(0, 0, 0, intval(date('m', $nextEvent['StartSysTime'])), intval(date('d', $nextEvent['StartSysTime'])) + 1, intval(date('Y', $nextEvent['StartSysTime'])));
                    } else {
                        $checkTime = $nextEvent['EndSysTime'];

                        if (($data['NextActionID'] != $nextEvent['ActionID']) || ($nextEvent['ActionID'] != 0)) {
                            $data['NextActionID'] = $nextEvent['ActionID'];
                            $data['EndSysTime'] = $nextEvent['StartSysTime'];
                            $dayEventFound = true;
                        }
                    }
                } while (($data['NextActionID'] == 0) && ($data['EndSysTime'] <= ($data['CheckSysTime'] + 86400 * 7)));
            }

            //Endpunkt liegt an einen der Folgetage !!
            if ($endPointFound == false) {
                if (($data['StartSysTime'] + 86400 * 6) < $data['CheckSysTime']) {
                    $data['CheckSysTime'] = ($data['StartSysTime'] + 86400 * 6);
                }

                $data['EndSysTime'] = mktime(0, 0, 0, intval(date('m', $data['CheckSysTime'])), intval(date('d', $data['CheckSysTime'])) + 1, intval(date('Y', $data['CheckSysTime'])));

                do {
                    $nextEvent = $this->GetWeeklyScheduleInfo($id, $data['EndSysTime'], true);

                    if (($nextEvent['ActionID'] == 0) && ($nextEvent['PreviousActionID'] == 0) && ($nextEvent['NextActionID'] == 0)) {
                        $data['EndSysTime'] = mktime(0, 0, 0, intval(date('m', $nextEvent['StartSysTime'])), intval(date('d', $nextEvent['StartSysTime'])) + 1, intval(date('Y', $nextEvent['StartSysTime'])));
                    } elseif (($nextEvent['NextActionID'] == 0) && ($nextEvent['PreviousActionID'] == 0)) {
                        $data['EndSysTime'] = mktime(0, 0, 0, intval(date('m', $nextEvent['StartSysTime'])), intval(date('d', $nextEvent['StartSysTime'])) + 1, intval(date('Y', $nextEvent['StartSysTime'])));
                    } else {
                        $data['EndSysTime'] = $nextEvent['StartSysTime'];
                        $data['NextActionID'] = $nextEvent['ActionID'];

                        if (($data['NextActionID'] == 0) || ($data['ActionID'] == $nextEvent['ActionID'])) {
                            $data['EndSysTime'] = $nextEvent['EndSysTime'];
                            $dayEventFound = true;
                        }
                    }
                } while ((($data['ActionID'] == $nextEvent['ActionID']) || ($data['NextActionID'] == $nextEvent['NextActionID'])) && ($data['EndSysTime'] <= ($data['CheckSysTime'] + 86400 * 7)));

                //Wenn es kein Abloesezeitpunkt (nur ein Event im Wochenplan) gibt !!
                if (($data['EndSysTime'] >= ($data['CheckSysTime'] + 86400 * 7)) && ($data['NextActionID'] == 0)) {
                    $data['NextActionID'] = $data['ActionID'];
                }

                //Wenn naechster Schaltpunkt = 0 ist --> Folgeevent suchen !!
                if (($data['NextActionID'] == 0) && ($data['ActionID'] != 0) && ($data['PreviousActionID'] != 0)) {
                    $checkTime = mktime(0, 0, 0, intval(date('m', $data['CheckSysTime'])), intval(date('d', $data['CheckSysTime'])) + 1, intval(date('Y', $data['CheckSysTime'])));

                    do {
                        $nextEvent = $this->GetWeeklyScheduleInfo($id, $checkTime, true);

                        if (($nextEvent['ActionID'] == 0) && ($nextEvent['PreviousActionID'] == 0) && ($nextEvent['NextActionID'] == 0)) {
                            $checkTime = mktime(0, 0, 0, intval(date('m', $nextEvent['StartSysTime'])), intval(date('d', $nextEvent['StartSysTime'])) + 1, intval(date('Y', $nextEvent['StartSysTime'])));
                        } elseif (($nextEvent['NextActionID'] == 0) && ($nextEvent['PreviousActionID'] == 0)) {
                            $checkTime = mktime(0, 0, 0, intval(date('m', $nextEvent['StartSysTime'])), intval(date('d', $nextEvent['StartSysTime'])) + 1, intval(date('Y', $nextEvent['StartSysTime'])));
                        } else {
                            $checkTime = $nextEvent['EndSysTime'];

                            if (($data['NextActionID'] != $nextEvent['ActionID']) || ($nextEvent['ActionID'] != 0)) {
                                $data['NextActionID'] = $nextEvent['ActionID'];
                                $data['EndSysTime'] = $nextEvent['StartSysTime'];
                                $dayEventFound = true;
                            }
                        }
                    } while (($data['NextActionID'] == 0) && ($data['EndSysTime'] <= ($data['CheckSysTime'] + 86400 * 7)));
                }

                //Wenn alle 3 Schaltpunkte gleich sind, dann Ueberpruefungszeitpunkt zurueckliefern (Kein oder nur EIN Event im Wochenplan)
                if (($data['ActionID'] == $data['PreviousActionID']) && ($data['ActionID'] == $data['NextActionID'])) {
                    $data['StartSysTime'] = $time;
                    $data['EndSysTime'] = $time;
                }
            }
        }

        $data['CheckSysTime'] = $time;
        $data['CheckTime'] = date('d.m.Y H:i:s', $data['CheckSysTime']);
        $data['StartTime'] = date('d.m.Y H:i:s', $data['StartSysTime']);
        $data['EndTime'] = date('d.m.Y H:i:s', $data['EndSysTime']);

        $data['Periode'] = $data['EndSysTime'] - $data['StartSysTime'];
        $data['PeriodeHours'] = floor($data['Periode'] / 3600);
        $data['PeriodeMinutes'] = $data['Periode'] / 60 % 60;
        $data['PeriodeSeconds'] = $data['Periode'] % 60;

        foreach ($eid['ScheduleActions'] as $n) {
            if ($n['ID'] == $data['ActionID']) {
                $data['ActionName'] = $n['Name'];
            }

            if ($n['ID'] == $data['PreviousActionID']) {
                $data['PreviousActionName'] = $n['Name'];
            }

            if ($n['ID'] == $data['NextActionID']) {
                $data['NextActionName'] = $n['Name'];
            }
        }

        return $data;
    }
}
