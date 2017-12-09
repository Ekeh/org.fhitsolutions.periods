<?php
use CRM_Periods_ExtensionUtil as E;

class CRM_Periods_Page_Periods extends CRM_Core_Page {

  public function run() {
    $cid = CRM_Utils_Request::retrieve("cid", "Positive", $this, FALSE,  0);

    $this->assign('cid', $cid );

    parent::run();
  }

}
