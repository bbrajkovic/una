<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 *
 * @defgroup    TridentStudio Trident Studio
 * @{
 */

bx_import('BxDolModuleQuery');

class BxDolStudioInstallerQuery extends BxDolModuleQuery
{
    function __construct()
    {
        parent::__construct();
    }

    function getRelationsBy($aParams = array())
    {
    	$sMethod = 'getAll';
    	$sWhereClause = "";

        switch($aParams['type']) {
            case 'module':
            	$sMethod = 'getRow';
                $sWhereClause .= $this->prepare(" AND `module`=?", $aParams['value']);
                break;
        }

        $sSql = "SELECT
                `id`,
                `module`,
                `on_install`,
                `on_uninstall`,
                `on_enable`,
                `on_disable`
            FROM `sys_modules_relations`
            WHERE 1" . $sWhereClause;

        return $this->$sMethod($sSql);
    }

    function insertModule(&$aConfig)
    {
        $sHelpUrl = isset($aConfig['help_url']) ? $aConfig['help_url'] : '';

        $sDependencies = '';
        if(isset($aConfig['dependencies']) && is_array($aConfig['dependencies']))
            $sDependencies = implode(',', $aConfig['dependencies']);

        $sQuery = $this->prepare("INSERT IGNORE INTO `sys_modules`(`type`, `name`, `title`, `vendor`, `version`, `help_url`, `path`, `uri`, `class_prefix`, `db_prefix`, `lang_category`, `dependencies`, `date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())", $aConfig['type'], $aConfig['name'], $aConfig['title'], $aConfig['vendor'], $aConfig['version'], $sHelpUrl, $aConfig['home_dir'], $aConfig['home_uri'], $aConfig['class_prefix'], $aConfig['db_prefix'], $aConfig['language_category'], $sDependencies);
        $iResult = (int)$this->query($sQuery);

        return $iResult > 0 ? (int)$this->lastId() : 0;
    }

    function insertModuleTrack($iModuleId, &$aFile)
    {
        $sQuery = $this->prepare("INSERT IGNORE INTO `sys_modules_file_tracks`(`module_id`, `file`, `hash`) VALUES(?, ?, ?)", $iModuleId, $aFile['file'], $aFile['hash']);
        $this->query($sQuery);
    }

    function getModuleTrackFiles($iModuleId)
    {
        $sQuery = $this->prepare("SELECT `file`, `hash` FROM `sys_modules_file_tracks` WHERE `module_id` = ?", $iModuleId);
        return $this->getAllWithKey($sQuery, "file");
    }

    function deleteModuleTrackFiles($iModuleId)
    {
        $sQuery = $this->prepare("DELETE FROM `sys_modules_file_tracks` WHERE `module_id` = ?", $iModuleId);
        return $this->query($sQuery);
    }

    function deleteModule($aConfig)
    {
        $sQuery = $this->prepare("SELECT `id` FROM `sys_modules` WHERE `vendor`=? AND `path`=? LIMIT 1", $aConfig['vendor'], $aConfig['home_dir']);
        $iId = (int)$this->getOne($sQuery);

        $sQuery = $this->prepare("DELETE FROM `sys_modules` WHERE `vendor`=? AND `path`=? LIMIT 1", $aConfig['vendor'], $aConfig['home_dir']);
        $this->query($sQuery);

        $this->deleteModuleTrackFiles($iId);

        return $iId;
    }
}

/** @} */
