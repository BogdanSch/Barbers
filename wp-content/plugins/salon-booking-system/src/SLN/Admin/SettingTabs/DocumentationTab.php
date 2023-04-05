<?php class SLN_Admin_SettingTabs_DocumentationTab extends SLN_Admin_SettingTabs_AbstractTab{
	protected $fields = array(
		'debug',
		'enable_sln_worker_role'
	);

    protected function postProcess() {

        if (isset($this->submitted['enable_sln_worker_role']) && $this->submitted['enable_sln_worker_role']) {
            SLN_UserRole_SalonWorker::addRole();
        } else {
            SLN_UserRole_SalonWorker::removeRole();
        }
    }
} ?>