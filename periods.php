<?php

require_once 'periods.civix.php';
use CRM_Periods_ExtensionUtil as E;

/**
 * Implementation of hook_civicrm_post
 *
 * @param $op
 * @param $objectName
 * @param $objectId
 * @param $objectRef
 * @throws Exception
 */
function periods_civicrm_post($op, $objectName, $objectId, &$objectRef) {
    try {
        if ($objectName != "Membership" && $objectName != "MembershipPayment" || $op == "delete") {
            return;
        }
        $membershipId = ($objectName == "Membership") ? $objectId : $objectRef->membership_id;
        $membership = getMembershipDetails($membershipId);
        if ($membership && $membership["membership_type_id.duration_unit"] == "lifetime") {
            return;
        }

        $params = [];
        // Create/ Edit membership period if Membership object is called
        if ($objectName == "Membership") {
            $params = processMembership($objectRef, $objectId, $op);
        }
        // Update membership period with contribution update if MembershipPayment object is called
        else if ($objectName == "MembershipPayment") {
            $params = processMembershipPayment($objectRef, $membershipId);
        }

        CRM_Periods_BAO_Periods::create($params);
    } catch (Exception $e) {
        throw new Exception($e->getMessage(), 400);
    }
}

/**
 * Manages creation, renewal or editing of membership period
 * @param $objectRef
 * @param $objectId
 * @param $op
 * @return array
 * @throws Exception
 */
function processMembership($objectRef, $objectId, $op) {
    $startDate=new DateTime($objectRef->start_date);
    $endDate=new DateTime($objectRef->end_date);
    $dateDifference = $startDate->diff($endDate);

    if ($dateDifference->format('%R%a') < 0) {
        throw new Exception(ts("End date must be the same or later than start date."));
        return;
    }

    $id = ($op == "create") ? null : getPeriodId($objectRef, $objectId, true);
    $params = [
        "start_date"        => $objectRef->start_date,
        "end_date"          => $objectRef->end_date,
        "membership_id"     => $objectRef->id,
        "contact_id"        => $objectRef->contact_id,
        "id"                => $id
    ];
    return $params;
}

/**
 * Updates membership period with contribution id
 * @param $objectRef
 * @param $membershipId
 * @return array
 */
function processMembershipPayment($objectRef, $membershipId) {
    $params = [
        "id"                => getPeriodId($objectRef, $membershipId),
        "contribution_id"   => $objectRef->contribution_id
    ];
    return $params;
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function Periods_civicrm_entityTypes(&$entityTypes)
{
    $entityTypes[] = array(
        'name' => 'Periods',
        'class' => 'CRM_Periods_DAO_Periods',
        'table' => 'civicrm_membership_periods',
    );
}

/**
 * This functions help to retrieve the id of the most recent period for a contact membership
 *
 * @param $objectRef
 * @param $id
 * @param bool $possibleRenewal
 * @return mixed
 */
function getPeriodId(&$objectRef, $id, $possibleRenewal = false) {
    $period = civicrm_api3("Periods", "get", [
        "membership_id" => $id,
        "sequential" => 1,
        "limit" => 1,
        "sort" => "id DESC"
    ]);
    if ($possibleRenewal && confirmRenewal($objectRef, $id, $period)) {
        $objectRef->start_date = $period["values"][0]["end_date"];
        return;
    }

    return $period["values"][0]["id"];
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
function confirmRenewal($objectRef, $membershipId, $period) {
    $membership = getMembershipDetails($membershipId);

    if (count($period["values"]) == 0) {
        return true;
    }

    // get the last period
    $interval = $membership['membership_type_id.duration_interval'];
    $interval .= " " . $membership['membership_type_id.duration_unit'];
    $date = date_create($period["values"][0]["end_date"]);
    $newEndDate = date_add($date, date_interval_create_from_date_string($interval));
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
function getMembershipDetails($membershipId) {
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

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function periods_civicrm_config(&$config) {
  _periods_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function periods_civicrm_xmlMenu(&$files) {
  _periods_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function periods_civicrm_install() {
  _periods_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function periods_civicrm_postInstall() {
  _periods_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function periods_civicrm_uninstall() {
  _periods_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function periods_civicrm_enable() {
  _periods_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function periods_civicrm_disable() {
  _periods_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function periods_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _periods_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function periods_civicrm_managed(&$entities) {
  _periods_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function periods_civicrm_caseTypes(&$caseTypes) {
  _periods_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function periods_civicrm_angularModules(&$angularModules) {
  _periods_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function periods_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _periods_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function periods_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function periods_civicrm_navigationMenu(&$menu) {
  _periods_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('The Page'),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _periods_civix_navigationMenu($menu);
} // */
