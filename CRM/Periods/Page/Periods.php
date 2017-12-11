<?php
use CRM_Periods_ExtensionUtil as E;

class CRM_Periods_Page_Periods extends CRM_Core_Page {

  public function run() {
    $cid = CRM_Utils_Request::retrieve("cid", "Positive", $this, FALSE,  0);

    $results = civicrm_api3('Periods', 'get', [
      'sequential' => 1,
      'return' => ["contribution_id.total_amount", "contribution_id.currency", "start_date", "end_date"],
      'contribution_id.contact_id' => $cid
    ]);
    // adding extra key called total to avoid issues with smarty
    // reading keys with dot - {$periods.contribution_id.total_amount}
    // this seems catered for in version 3 of smarty - {$periods["contribution_id.total_amount"]}
    foreach ($results['values'] as $key => $value) {
        $results["values"][$key]["total"] = $value["contribution_id.total_amount"];
        $results["values"][$key]["currency"] = $value["contribution_id.currency"];
    }

    $this->assign('cid', $cid );
    $this->assign('periods', $results["values"] );



      parent::run();
  }

}
