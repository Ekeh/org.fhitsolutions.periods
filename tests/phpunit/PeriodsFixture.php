<?php

class PeriodsFixture {

    /**
     * Generate a new contact
     * @return array
     */
    public static function setupContact() {
        $contact = civicrm_api3('Contact', 'create', [
            'sequential' => 1,
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'contact_type' => 'Individual'
        ]);
        return $contact;
    }

    /**
     * Creates new membership record
     *
     * @param array $data
     *      record to be used for overriding default if provided
     * @return array
     */
    public static function createMembership($contactId, $data = []) {
        $memberData = [
            'sequential' => 1,
            'membership_type_id' => 'General',
            'contact_id' => $contactId
        ];
        $memberData = array_merge($memberData, $data);
        $membership = civicrm_api3('Membership', 'create', $memberData);
        return $membership;
    }

    /**
     * Retrieve most recent Membership Period
     * @param $contactId
     *      Contact for which membership period is to be retrieved
     * @return array
     */
    public static function getLastMembershipPeriods($contactId) {
        $periodsData = [
            'sequential' => 1,
            'contact_id' => $contactId,
            'options' => ['limit' => 1, 'sort' => "id DESC"],
        ];
        $periods = civicrm_api3('Periods', 'get', $periodsData);
        return $periods;
    }
}