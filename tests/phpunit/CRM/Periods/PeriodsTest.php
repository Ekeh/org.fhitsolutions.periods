<?php

use CRM_Periods_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This is a test implementation for ensuring proper management of contact membership
 * period each time membership is created and or renewed
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Periods_PeriodsTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface
{
    const DURATION_INTERVAL = 2;
    const DURATION_UNIT = "year";
    const DEFAULT_FEE = 0.00;
    const MINIMUM_FEE = 1200.00;

    private $contact;
    private $domain;
    private $membershipType;
    private $membershipTypeLifetime;
    private $contribution;

    /**
     * Initialize the domain
     *
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Initialize the member type members
     * @param mixed $membershipType
     * @param string $name
     *      Used to determine lifetime membership type
     */
    public function setMembershipType($membershipType, $name = "")
    {
        if (!$name) {
            $this->membershipType = $membershipType;
        } else {
            $this->membershipTypeLifetime = $membershipType;
        }
    }

    public function setUpHeadless()
    {
        // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
        // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
        return \Civi\Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Generate a new contact
     *
     * @return array
     */
    private static function setupContact()
    {
        $contact = civicrm_api3('Contact', 'create', [
            'sequential' => 1,
            'first_name' => 'Firstname',
            'last_name' => 'Lastname',
            'contact_type' => 'Individual'
        ]);
        return $contact;
    }

    /**
     * Create new domain if none exist on the data table
     */
    private function setupDomain()
    {
        $domain = civicrm_api3('Domain', 'get', [
            'sequential' => 1,
            'options' => ['limit' => 1]
        ]);
        if ($domain["count"] == 0) {
            $newDomain = civicrm_api3('Domain', 'create', [
                'sequential' => 1,
                'name' => "Default Domain Name",
                'domain_version' => "4.7.27",
            ]);
            $this->setDomain($newDomain);
        } else {
            $this->setDomain($domain);
        }
    }

    /**
     * @param mixed $contribution
     */
    public function setContribution($contribution)
    {
        $this->contribution = $contribution;
    }

    /**
     * Create membership type base on domain if no record exist
     *
     * @param string $name
     */
    private function setupMembershipType($name = "")
    {
        $membershipType = civicrm_api3('MembershipType', 'get', [
            'sequential' => 1,
            'options' => ['limit' => 1]
        ]);
        if ($membershipType["count"] == 0 || $name) {
            $newMembershipType = civicrm_api3('MembershipType', 'create', [
                'sequential' => 1,
                'domain_id' => $this->domain["values"][0]["id"],
                'member_of_contact_id' => $this->contact["values"][0]["id"],
                'financial_type_id' => "Donation",
                'minimum_fee' => ($name) ? self::DEFAULT_FEE : self::MINIMUM_FEE,
                'duration_unit' => ($name) ? $name : self::DURATION_UNIT,
                'duration_interval' => self::DURATION_INTERVAL ,
                'period_type' => "rolling",
                'name' => ($name) ? $name : "General",
                'description' => "Regular annual membership.",
            ]);
            $this->setMembershipType($newMembershipType, $name);
        } else {
            $this->setMembershipType($membershipType);
        }
    }

    /**
     * Creates new membership record
     *
     * @param int $membershipTypeId
     *      id of membership type used to cater for lifetime membership
     * @param array $data
     *      record to be used for overriding default if provided
     * @return array
     */
    private function createMembership($membershipTypeId, $data = [])
    {
        $memberData = [
            'sequential' => 1,
            'membership_type_id' => $membershipTypeId,
            'contact_id' => $this->contact["values"][0]["id"]
        ];

        $memberData = array_merge($memberData, $data);
        $membership = civicrm_api3('Membership', 'create', $memberData);
        return $membership;
    }

    /**
     * Retrieve most recent Membership Period
     *
     * @param $contactId
     *      Contact for which membership period is to be retrieved
     * @param array $data
     *      Optional parameters for filtering membership period details
     * @return array
     */
    private static function getLastMembershipPeriods($contactId, $data = [])
    {
        $periodsData = [
            'sequential' => 1,
            'contact_id' => $contactId,
            'options' => ['limit' => 1, 'sort' => "id DESC"],
        ];
        $periodsData = array_merge($periodsData, $data);
        $periods = civicrm_api3('Periods', 'get', $periodsData);
        return $periods;
    }

    private function setupContribution() {
        $contribution = civicrm_api3('Contribution', 'create', array(
            'sequential' => 1,
            'financial_type_id' => "Donation",
            'total_amount' => self::MINIMUM_FEE,
            'contact_id' => $this->contact["values"][0]["id"],
        ));

        $this->setContribution($contribution);
    }

    private function createPayment($membershipId) {
        $payment = civicrm_api3('MembershipPayment', 'create', array(
            'sequential' => 1,
            'membership_id' => $membershipId,
            'contribution_id' => $this->contribution["values"][0]["id"],
        ));
        return $payment;
    }

    /**
     * Generate required fixtures for carrying out test
     */
    public function setUp()
    {
        $this->contact = self::setupContact();
        $this->setupDomain();
        $this->setupMembershipType();

        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Calculate new date based on unit and interval
     * @param $unit
     * @param $interval
     * @param $startDate
     * @return DateTime|false
     */
    private function getDateInterval($unit, $interval, $startDate) {
        $interval = $interval . " " . $unit;
        $date = date_create($startDate);
        $newEndDate = date_add($date, date_interval_create_from_date_string($interval));
        return $newEndDate;
    }

    /**
     * Period Creation test implementation
     */
    public function testPeriodsCreation()
    {
        // Given that contact already exist
        // When i create a new membership record without specifying start and end date
        $newMembership = $this->createMembership($this->membershipType['values'][0]["id"]);

        // Then membership period should be created successfully with membership start and end date
        $expectedData = [
            "start_date" => $newMembership["values"][0]["start_date"],
            "end_date" => $newMembership["values"][0]["end_date"]
        ];
        $memberPeriod = self::getLastMembershipPeriods($this->contact["values"][0]["id"]);
        $periodData = [
            "start_date" => str_replace("-", "", $memberPeriod["values"][0]["start_date"]),
            "end_date" => str_replace("-", "", $memberPeriod["values"][0]["end_date"])
        ];

        $this->assertEquals($expectedData, $periodData);

        // And end date should be the current year plus year interval as duration unit
        $startDate = $memberPeriod["values"][0]["start_date"];
        $expectedEndDate = $this->getDateInterval(
            $this->membershipType["values"][0]["duration_unit"],
            $this->membershipType["values"][0]["duration_interval"],
            $startDate
        );
        $this->assertEquals(
            date('Y', strtotime($memberPeriod["values"][0]["end_date"])),
            date_format($expectedEndDate, 'Y')
        );
    }

    /**
     * Ensure period creation fails if invalid date range is specified
     */
    public function testInvalidPeriodCreation() {
        // Given that contact already exist
        // When i create a new membership record by specifying end_date that is less than start_date
        try {
            $this->createMembership(
                $this->membershipType['values'][0]["id"],
                [
                    "start_date" => "2017-01-01",
                    "end_date" => "2014-01-01"
                ]
            );
        } catch (Exception $e) {
            // Then an exception should be raised and the process terminated
            $this->assertEquals($e->getMessage(), "End date must be the same or later than start date.");
        }
    }

    /**
     * Test membership renewal process with new period creation
     */
    public function testMembershipRenewal() {
        // Given that membership record was previously created
        $oldMembership = $this->createMembership($this->membershipType['values'][0]["id"]);
        $oldPeriod = $this->getLastMembershipPeriods($this->contact["values"][0]["id"]);
        // When new membership creation is requested with end date that equal next interval
        $nextDate = $this->getDateInterval(
            $this->membershipType["values"][0]["duration_unit"],
            $this->membershipType["values"][0]["duration_interval"],
            $oldMembership["values"][0]["end_date"]
        );
        $nextDate = date_format($nextDate, 'Y-m-d');
        $nextStartDate = date_format(
            $this->getDateInterval("day", 1, $oldPeriod["values"][0]["end_date"]),
            'Y-m-d'
        );

        $this->createMembership(
            $this->membershipType['values'][0]["id"],
            [
                "id" => $oldMembership["values"][0]["id"],
                "start_date" => $oldMembership["values"][0]["start_date"],
                "end_date" => $nextDate,
            ]
        );

        $newPeriod = $this->getLastMembershipPeriods($this->contact["values"][0]["id"]);

        // Then the most recent membership period for contact should have new record with
        // start_date as oldMember's end_date and end_date as the new interval
        $this->assertEquals($nextStartDate, $newPeriod["values"][0]["start_date"]);
        $this->assertEquals($nextDate, $newPeriod["values"][0]["end_date"]);
    }

    /**
     * Member period editing functionality test
     */
    public function testPeriodsEditing() {
        // Given that membership record was previously created
        $oldMembership = $this->createMembership($this->membershipType['values'][0]["id"]);


        // When i changed the start or end date of contact membership
        $newStartDate = date_format(
            $this->getDateInterval("month", 3, $oldMembership["values"][0]["start_date"]),
            'Y-m-d'
        );
        $newEndDate = date_format(
            $this->getDateInterval("month", 3, $oldMembership["values"][0]["end_date"]),
            'Y-m-d'
        );
        $this->createMembership(
            $this->membershipType['values'][0]["id"],
            [
                "id" => $oldMembership["values"][0]["id"],
                "start_date" => $newStartDate,
                "end_date" => $newEndDate
            ]
        );
        $newPeriod = $this->getLastMembershipPeriods($this->contact["values"][0]["id"]);

        // Then the last period should have been updated to the new
        $this->assertEquals($newStartDate, $newPeriod["values"][0]["start_date"]);
        $this->assertEquals($newEndDate, $newPeriod["values"][0]["end_date"]);
    }

    /**
     * Lifetime membership test integration
     */
    public function testLifetimeMembership() {
        // Given that membership type lifetime already exist
        $this->setupMembershipType("Lifetime");
        // When lifetime membership type is created for contact
        $membership = $this->createMembership($this->membershipTypeLifetime['values'][0]["id"]);

        $period = $this->getLastMembershipPeriods(
            $this->contact["values"][0]["id"], [
                "membership_id" => $membership["values"][0]["id"]
            ]
        );
        // Then no membership period should be created for member
        $this->assertEquals($period["count"], 0);
    }

    /**
     * Member period contribution test
     */
    public function testMembershipPeriodContribution() {
        // Given that membership and contribution exist
        $this->setupContribution();
        $membership = $this->createMembership($this->membershipType["values"][0]["id"]);
        // When contribution was made for the membership data
        $membershipPayment =  $this->createPayment($membership["values"][0]["id"]);
        $periods = $this->getLastMembershipPeriods(
            $this->contact["values"][0]["id"],
            [
                "membership_id" => $membership["values"][0]["id"]
            ]
        );

        // Then period should indicate link to contribution
        $this->assertEquals(
            $membershipPayment["values"][0]["contribution_id"],
            $periods["values"][0]["contribution_id"]
        );
    }
}
