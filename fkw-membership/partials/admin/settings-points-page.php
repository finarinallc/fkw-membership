<?php
// Get the active tab from the $_GET param
$default_tab = 'points_intervals';
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;
?>
    <nav class="nav-tab-wrapper">
        <a href="?page=<?php echo $this->module_settings_id; ?>&tab=points_intervals" class="nav-tab<?php if( $tab === 'points_intervals' ):?> nav-tab-active<?php endif; ?>">Point Intervals</a>
        <a href="?page=<?php echo $this->module_settings_id; ?>&tab=points_usermanagement" class="nav-tab<?php if( $tab === 'points_usermanagement' ):?> nav-tab-active<?php endif; ?>">Manage User Points</a>
    </nav>
    <?php if( $tab === 'points_usermanagement' ) { ?>
    <div class="tab-content maps">
        <h2>Manage User Points</h2>
        <?php require_once FKWMEMBERSHIP_PLUGIN_BASENAME . 'partials/admin/settings-points-page-usermanagement-tab.php'; ?>
    </div>
    <?php } else { ?>
    <div class="tab-content">
        <h2>Point Automatic Award Intervals</h2>
        <?php require_once FKWMEMBERSHIP_PLUGIN_BASENAME . 'partials/admin/settings-points-page-intervals-tab.php'; ?>
    </div>
    <?php } ?>

</div>
<?php
