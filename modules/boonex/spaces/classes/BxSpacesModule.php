<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defdroup    Spaces Spaces
 * @indroup     UnaModules
 *
 * @{
 */

/**
 * Spaces profiles module.
 */
define('BX_SPS_LEVELS_LIMIT', 1);

class BxSpacesModule extends BxBaseModGroupsModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
       
        $this->_aSearchableNamesExcept[] = $this->_oConfig->CNF['FIELD_JOIN_CONFIRMATION'];
    }
    
    public function serviceEntityDelete ($iContentId = 0)
    {
        $iContentId = $this->_getContent($iContentId, false);
        if($iContentId === false)
            return false;
        $iCount = $this->_oDb->getCountEntriesByParent($iContentId);
        if ($iCount > 0)
            return MsgBox(_t('_bx_spaces_err_delete_child_presend'));
        return $this->_serviceEntityForm ('deleteDataForm', $iContentId);
    }
    
    public function serviceEntityParent ($iContentId = 0)
    {
        return $this->_serviceTemplateFunc ('entryParent', $iContentId);
    }
    
    public function serviceEntityChilds ($iContentId = 0)
    {
        return $this->_serviceTemplateFunc ('entryChilds', $iContentId);
    }
    
    /**
     * Get possible recipients for start conversation form
     */
    public function actionAjaxGetParentSpace ()
    {
        $sTerm = bx_get('term');
        $iContentId = bx_get('id');
        $a = $this->getListSpacesForParent($sTerm, $iContentId, 10);
        header('Content-Type:text/javascript; charset=utf-8');
        echo(json_encode($a));
    }
     
    public function checkAllowedSubscribeAdd (&$aDataEntry, $isPerformAction = false)
    {
        return parent::_checkAllowedSubscribeAdd ($aDataEntry, $isPerformAction);
    }
    
    public function getListSpacesForParent ($sTerm, $iContentId, $iLimit)
    {
        if (!isLogged())
            return false;
        
        $aRv = array();
        $aTmp = $this->_oDb->searchByTermForParentSpace(bx_get_logged_profile_id(), $iContentId, BX_SPS_LEVELS_LIMIT, $sTerm, $iLimit);
        foreach ($aTmp as $aSpace) {
            $oProfile = BxDolProfile::getInstance($aSpace['profile_id']);

            $aRv[] = array (
                'label' => $this->serviceProfileName($aSpace['content_id']),
                'value' => $aSpace['profile_id'],
                'url' => $oProfile->getUrl(),
                'thumb' => $oProfile->getThumb(),
                'unit' => $oProfile->getUnit(0, array('template' => 'unit_wo_info'))
            );
        }
        return $aRv;
    }
}

/** @} */
