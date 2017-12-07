<?php

require_once 'periods.civix.php';
use CRM_Periods_ExtensionUtil as E; 

/**
 *
 */
/**
 * Implementation of hook_civicrm_post
 *
 * @param $op
 *      Type of operation carried out
 * @param $objectName
 *      CiviCRM object carrying out operations
 * @param $objectId
 *      Id of the object
 * @param $objectRef
 *      Reference to object
 */
function periods_civicrm_post($op, $objectName, $objectId, &$objectRef) {
    if ($objectName != "Membership" && $objectName != "MembershipPayment" || $op == "delete") {
        return;
    }

    $params = [];
    // Create/ Edit membership period if Membership object is called
    if ($objectName == "Membership") {
        $params = [
            "start_date"        => $objectRef->start_date,
            "end_date"          => $objectRef->end_date,
            "membership_id"     => $objectRef->id,
            "contact_id"        => $objectRef->contact_id,
            "id"                => ($op == "create") ? : getPeriodId($objectRef->id)
        ];
    }
    // Update membership period with contribution update if MembershipPayment object is called
    if ($objectName == "MembershipPayment") {
        $params = [
            "id"                => getPeriodId($objectRef->membership_id),
            "contribution_id"   => $objectRef->contribution_id
        ];
    }
    CRM_Periods_BAO_Periods::create($params);
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
 * @param $id
 *      Id of the membership data related to the period
 * @return mixed
 */
function getPeriodId($id) {
    try {
        $search = [
            "membership_id" => $id,
            "return" => ["id"],
            "options" => ["sort" => "id DESC", "limit" => 1],
        ];
        $result = civicrm_api3('Periods', 'get', $search);
        return $result["id"];
    } catch (CiviCRM_API3_Exception $e) {
        $error = $e->getMessage();
    }
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
