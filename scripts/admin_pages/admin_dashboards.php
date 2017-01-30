<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}
if (isset($this->get['tx_multishop_pi1']['dashboard_id']) && is_numeric($this->get['tx_multishop_pi1']['dashboard_id']) && $this->get['tx_multishop_pi1']['dashboard_id']>0) {
    $dashboard=mslib_befe::getRecord($this->get['tx_multishop_pi1']['dashboard_id'], 'tx_multishop_dashboard', 'id', array('status=1 AND deleted=0'));
    if (is_array($dashboard) && $dashboard['id']) {
        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('multishop') . 'pi1/classes/class.tx_mslib_dashboard.php');
        $mslib_dashboard = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mslib_dashboard');
        $mslib_dashboard->init($this);
        $mslib_dashboard->setSection($dashboard['id']);
        $mslib_dashboard->renderWidgets();
        $dashboard_content = $mslib_dashboard->displayDashboard();
        if ($dashboard_content) {
            $GLOBALS['TSFE']->additionalHeaderData['admin_dashboards_js'] = '
            <script type="text/javascript" data-ignore="1">
                var admin_label_add_widget=\''.$this->pi_getLL('admin_label_add_widget').'\';
                var admin_label_change_layout=\''.$this->pi_getLL('admin_label_change_layout').'\';
                var saveNewLayoutAjaxURL = \''.mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_dashboards&tx_multishop_pi1[action]=save_dashboard_layout').'\';
                var addWidgetAjaxURL = \''.mslib_fe::typolink($this->shop_pid . ',2002', '&tx_multishop_pi1[page_section]=admin_dashboards&tx_multishop_pi1[action]=add_new_widget').'\';
            </script>
            <script type="text/javascript" src="./typo3conf/ext/multishop/js/admin_dashboards.js" data-ignore="1"></script>' . "\n";
            $content .= '<div id="dashboardWrapper" class="panel panel-default" data-dashboard-id="'.$dashboard['id'].'" data-dashboard-layout="'.$dashboard['dashboard_layout'].'">
                <div class="panel-heading">
                    <h3>'.$dashboard['header_title'].'</h3>
                    <div class="btnWrapper">
                        <button id="addWidget" class="btn btn-primary btn-default" data-toggle="modal" data-target="#dashboardModal"><i class="fa fa-plus"></i> '.$this->pi_getLL('admin_label_add_widget').'</button> 
                        <button id="changeLayout" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#dashboardModal"><i class="fa fa-columns"></i> '.$this->pi_getLL('admin_label_change_layout').'</button>
                    </div>
                </div>
                <div class="panel-body">
                '.$dashboard_content.'
                </div>
            </div>
            <div class="modal fade" id="dashboardModal" tabindex="-1" role="dialog" aria-labelledby="dashboardModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="dashboardModalLabel"></h4>
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">'.$this->pi_getLL('cancel').'</button>
                            <button type="button" id="saveChanges" class="btn btn-primary">'.$this->pi_getLL('save').'</button>
                        </div>
                    </div>
                </div>
            </div>
            ';
        }
    } else {
        $content .= '<p>Dahsboard not found</p>';
    }
    $content .= '<p class="extra_padding_bottom"><a class="btn btn-success msAdminBackToCatalog" href="' . mslib_fe::typolink() . '">' . $this->pi_getLL('admin_close_and_go_back_to_catalog') . '</a></p>';
}
?>