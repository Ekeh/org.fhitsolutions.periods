<?php

/**
 * Created by PhpStorm.
 * User: ajesamson
 * Date: 12/17/17
 * Time: 12:33 AM
 */
class PeriodUtil
{
    /**
     * This functions help to retrieve the id of the most recent period for a contact membership
     *
     * @param $objectRef
     * @param $id
     * @param bool $possibleRenewal
     * @return mixed
     */
    public static function getPeriodId(&$objectRef, $id, $possibleRenewal = false) {
        $period = civicrm_api3("Periods", "get", [
            "membership_id" => $id,
            "sequential" => 1,
            "limit" => 1,
            "sort" => "id DESC"
        ]);
        if ($possibleRenewal && self::confirmRenewal($objectRef, $id, $period)) {
            $newStartDate = self::getDateInterval("day", "1", $period["values"][0]["end_date"]);
            $objectRef->start_date =  date_format($newStartDate, "Y-m-d");
            return;
        }

        return $period["values"][0]["id"];
    }

    /**
     * Calculate the next date based on interval, unit and date
     *
     * @param $unit
     * @param $interval
     * @param $startDate
     * @return DateTime|false
     */
    public static function getDateInterval($unit, $interval, $startDate) {
        $interval = $interval . " " . $unit;
        $date = date_create($startDate);
        $newEndDate = date_add($date, date_interval_create_from_date_string($interval));
        return $newEndDate;
    }

    /**
     * Confirm if renewal or new record should be created
     * based on membership duration interval and unit
     *
     * @param $objectRef
     * @param $membershipId
     * @param $period
     * @return bool
     */
    public static function confirmRenewal($objectRef, $membershipId, $period) {
        $membership = self::getMembershipDetails($membershipId);

        if (count($period["values"]) == 0) {
            return true;
        }

        // get the last period
        $newEndDate = self::getDateInterval(
            $membership['membership_type_id.duration_unit'],
            $membership['membership_type_id.duration_interval'],
            $period["values"][0]["end_date"]
        );
        $newEndDate = date_format($newEndDate, 'Y-m-d');
        if ($objectRef->end_date == $newEndDate) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve membership details
     *
     * @param $membershipId
     * @return array
     */
    public static function getMembershipDetails($membershipId) {
        $membership = civicrm_api3("Membership",'getsingle',array(
            "id"        => $membershipId,
            "return"    => [
                "end_date",
                "membership_type_id.duration_interval",
                "membership_type_id.duration_unit"
            ],
        ));
        return $membership;
    }
}