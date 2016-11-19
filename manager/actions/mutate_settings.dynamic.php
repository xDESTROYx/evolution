<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br />Please use the MODX Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('settings')) $modx->webAlertAndQuit($_lang['error_no_privileges']);

include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/functions.inc.php');

// check to see the edit settings page isn't locked
$rs = $modx->db->select('username', $modx->getFullTableName('active_users'), "action=17 AND internalKey!='".$modx->getLoginUserID()."'");
    if ($username = $modx->db->getValue($rs)) {
            $modx->webAlertAndQuit(sprintf($_lang['lock_settings_msg'],$username));
    }
// end check for lock

// reload system settings from the database.
// this will prevent user-defined settings from being saved as system setting
$settings = array();
include_once(MODX_MANAGER_PATH . 'includes/default_config.php');
$rs = $modx->db->select('setting_name, setting_value', '[+prefix+]system_settings');
while ($row = $modx->db->getRow($rs)) {
    $settings[$row['setting_name']] = $row['setting_value'];
}
$settings['filemanager_path'] = preg_replace('@^' . MODX_BASE_PATH . '@', '[(base_path)]', $settings['filemanager_path']);
$settings['rb_base_dir']      = preg_replace('@^' . MODX_BASE_PATH . '@', '[(base_path)]', $settings['rb_base_dir']);

extract($settings, EXTR_OVERWRITE);

// load languages and keys
$lang_keys = array();
$dir = dir('includes/lang');
while ($file = $dir->read()) {
    if(strpos($file, '.inc.php')>0) {
        $endpos = strpos ($file, '.');
        $languagename = substr($file, 0, $endpos);
        $lang_keys[$languagename] = get_lang_keys($file);
    }
}
$dir->close();
$displayStyle = ($_SESSION['browser']==='modern') ? 'table-row' : 'block' ;
?>
<style type="text/css">
  table th {text-align:left; vertical-align:top;}
</style>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<script type="text/javascript" src="actions/mutate_settings/functions.js"></script>
<script type="text/javascript">
var displayStyle = '<?php echo $displayStyle; ?>';
var lang_chg = '<?php echo $_lang['confirm_setting_language_change']; ?>';
</script>
<form name="settings" action="index.php?a=30" method="post">
  <h1 class="pagetitle">
  <span class="pagetitle-icon">
    <i class="fa fa-sliders"></i>
  </span>
  <span class="pagetitle-text">
    <?php echo $_lang['settings_title']; ?>
  </span>
    </h1>
    <div id="actions">
          <ul class="actionButtons">
              <li id="Button1" class="transition">
                <a href="#" onclick="documentDirty=false; document.settings.submit();">
                    <img src="<?php echo $_style["icons_save"]?>" /> <?php echo $_lang['save']; ?>
                </a>
              </li>
              <li id="Button5" class="transition">
                <a href="#" onclick="documentDirty=false;document.location.href='index.php?a=2';">
                    <img src="<?php echo $_style["icons_cancel"]?>" /> <?php echo $_lang['cancel']; ?>
                </a>
              </li>
        </ul>
    </div>

<div style="margin: 0 10px 0 20px">
    <input type="hidden" name="site_id" value="<?php echo $site_id; ?>" />
    <input type="hidden" name="settings_version" value="<?php echo $modx->getVersionData('version'); ?>" />
    <!-- this field is used to check site settings have been entered/ updated after install or upgrade -->
    <?php if(!isset($settings_version) || $settings_version!=$modx->getVersionData('version')) { ?>
    <div class='sectionBody'><p><?php echo $_lang['settings_after_install']; ?></p></div>
    <?php } ?>
    <div class="tab-pane" id="settingsPane">
      <script type="text/javascript">
        tpSettings = new WebFXTabPane(document.getElementById('settingsPane'), <?php echo $modx->config['remember_last_tab'] == 1 ? 'true' : 'false'; ?> );
    </script>

<?php
    include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/tab1_site_settings.inc.php');
    include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/tab2_furl_settings.inc.php');
    include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/tab3_user_settings.inc.php');
    include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/tab4_manager_settings.inc.php');
    include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/tab6_filemanager_settings.inc.php');
    include_once(MODX_MANAGER_PATH . 'actions/mutate_settings/tab7_filebrowser_settings.inc.php');
?>
    </div>
</div>
</form>
<script>
    jQuery('input:radio').change(function()   {documentDirty=true;});
    jQuery('#furlRowOn').change(function()    {jQuery('.furlRow').fadeIn();});
    jQuery('#furlRowOff').change(function()   {jQuery('.furlRow').fadeOut();});
    jQuery('#udPermsOn').change(function()    {jQuery('.udPerms').slideDown();});
    jQuery('#udPermsOff').change(function()   {jQuery('.udPerms').slideUp();});
    jQuery('#editorRowOn').change(function()  {jQuery('.editorRow').slideDown();});
    jQuery('#editorRowOff').change(function() {jQuery('.editorRow').slideUp();});
    jQuery('#rbRowOn').change(function()      {jQuery('.rbRow').fadeIn();});
    jQuery('#rbRowOff').change(function()     {jQuery('.rbRow').fadeOut();});
    jQuery('#useSmtp').change(function()      {jQuery('.smtpRow').fadeIn();});
    jQuery('#useMail').change(function()      {jQuery('.smtpRow').fadeOut();});
    jQuery('#captchaOn').change(function()    {jQuery('.captchaRow').fadeIn();});
    jQuery('#captchaOff').change(function()   {jQuery('.captchaRow').fadeOut();});
</script>
<?php
if (is_numeric($_GET['tab'])) {
    echo '<script type="text/javascript">tpSettings.setSelectedIndex( '.$_GET['tab'].' );</script>';
}
