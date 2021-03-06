<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defdroup    Channels Channels
 * @indroup     UnaModules
 *
 * @{
 */

/**
 * Channels profiles module.
 */

class BxCnlModule extends BxBaseModGroupsModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }
   
    public function checkAllowedSubscribeAdd (&$aDataEntry, $isPerformAction = false)
    {
        return parent::_checkAllowedSubscribeAdd ($aDataEntry, $isPerformAction);
    }
    
    function processHashtag($sHashtag, $sModuleName, $iContentId, $iAuthorId)
    {
        $mixedCnlId = $this->_oDb->getChannelIdByName($sHashtag);
        if (empty($mixedCnlId)){
            $CNF = &$this->_oConfig->CNF;
            $oAccountQuery = BxDolAccountQuery::getInstance();
            $aOperators = $oAccountQuery->getOperators();
            if(count($aOperators) > 0){
                $oProfile = BxDolProfile::getInstanceByAccount($aOperators[0]);
                $aContent = $this->serviceEntityAdd($oProfile->id(), array($CNF['FIELD_NAME'] => $sHashtag));
                if (isset($aContent['content']) && isset($aContent['content']['id']))
                    $mixedCnlId = $aContent['content']['id'];
            }
        }
        
        if (!empty($mixedCnlId)){
            if (0 == (int) $this->_oDb->checkContentInChannel($iContentId, $mixedCnlId, $sModuleName, $iAuthorId)){
                $iId = $this->_oDb->addContentToChannel($iContentId, $mixedCnlId, $sModuleName, $iAuthorId);
                $oModule = BxDolModule::getInstance($sModuleName);
                $aInfo = $oModule->_getContent($iContentId);
                if (is_array($aInfo)){
                    $oDolProfileQuery = BxDolProfileQuery::getInstance();
                    $iProfileInfo = $oDolProfileQuery->getProfileByContentAndType($mixedCnlId, $this->_aModule['name']);
                    if(is_array($iProfileInfo)){
                        $sPrivacyKey = 'allow_view_to';
                        if ($sModuleName == 'bx_timeline'){
                            $sPrivacyKey ='object_privacy_view';
                        }
                        bx_alert($this->_aModule['name'], 'hashtag_added', $iId, $iProfileInfo['id'], array('object_author_id' => $iAuthorId, 'privacy_view' => $aInfo[1][$sPrivacyKey]));
                        bx_alert($this->_aModule['name'], 'hashtag_added_notif', $mixedCnlId, $iProfileInfo['id'], array('object_author_id' => $iAuthorId, 'privacy_view' => $aInfo[1][$sPrivacyKey], 'subobject_id' => $iId));
                    }
                }
            }
        }
    }
    
    function removeContentFromChannel($iContentId, $sModuleName)
    {
        $oDolProfileQuery = BxDolProfileQuery::getInstance();
        
        $aData = $this->_oDb->getDataByContent($iContentId, $sModuleName);
        foreach ($aData as $aRow) {
            $iProfileInfo = $oDolProfileQuery->getProfileByContentAndType($aRow['cnl_id'], $this->_aModule['name']);
            if(is_array($iProfileInfo)){
                bx_alert($this->_aModule['name'], 'hashtag_deleted', $aRow['id'], $iProfileInfo['id']);
                bx_alert($this->_aModule['name'], 'hashtag_deleted_notif', $aRow['cnl_id'], $iProfileInfo['id'], array('subobject_id' => $aRow['id']));
            }
        }
        
        return $this->_oDb->removeContentFromChannel($iContentId, $sModuleName);
    }
    
    function serviceSearchResultByHashtag($iContentId = 0)
    {
        $CNF = &$this->_oConfig->CNF;
        
        $oSearch = new BxTemplSearch();
        $oSearch->setLiveSearch(0);
        $oSearch->setMetaType('keyword');
        $aContentInfo = $this->_oDb->getContentInfoById(bx_get('id'));
        $_GET['keyword'] = $aContentInfo[$CNF['FIELD_NAME']];
        $sCode = $oSearch->response();
        if (!$sCode)
            $sCode = $oSearch->getEmptyResult();
        
        return $sCode;
    }
    
    /**
     * Data for Timeline module
     */
    public function serviceGetTimelineData()
    {
        $sModule = $this->_aModule['name'];

        return array(
            'handlers' => array(
                array('group' => $sModule . '_hastag', 'type' => 'insert', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_added', 'module_name' => $sModule, 'module_method' => 'get_timeline_post_hashtag', 'module_class' => 'Module',  'groupable' => 0, 'group_by' => ''),
                array('group' => $sModule . '_hastag', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_deleted')
            ),
            'alerts' => array(
                array('unit' => $sModule, 'action' => 'hashtag_added'),
                array('unit' => $sModule, 'action' => 'hashtag_deleted')
            )
        );
    }
    
    public function serviceGetTimelinePostHashtag($aEvent, $aBrowseParams = array())
    {
        if(empty($aEvent) || !is_array($aEvent))
            return '';
        
        $aContentEvent = $this->_oDb->getContentById($aEvent['object_id']);
        if(empty($aContentEvent) || !is_array($aContentEvent))
            return '';

        $oModule = BxDolModule::getInstance($aContentEvent['module_name']);
        if ($oModule){
             $aTmp = $oModule->serviceGetTimelinePost(array('object_id' => $aContentEvent['content_id']));
             if ($aContentEvent['module_name'] == 'bx_timeline')
                $aTmp['owner_id'] =  $aEvent['owner_id'];
             return $aTmp;
        }
        
        return '';
    }
    
    /**
     * Data for Notifications module
     */
    public function serviceGetNotificationsData()
    {      
        $a = parent::serviceGetNotificationsData();
        
        $sModule = $this->_aModule['name'];

        $a['handlers'][] = array('group' => $sModule . '_hastag_notif', 'type' => 'insert', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_added_notif', 'module_name' => $sModule, 'module_method' => 'get_notifications_post_hashtag', 'module_class' => 'Module');
        $a['handlers'][] = array('group' => $sModule . '_hastag_notif', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'hashtag_deleted_notif');

        $a['alerts'][] = array('unit' => $sModule, 'action' => 'hashtag_added_notif');
        $a['alerts'][] = array('unit' => $sModule, 'action' => 'hashtag_deleted_notif');
        
        return $a;
    }

    public function serviceGetNotificationsPostHashtag($aEvent)
    {
         if(empty($aEvent) || !is_array($aEvent))
            return '';
         
         $aContentEvent = $this->_oDb->getContentById($aEvent['subobject_id']);
         if(empty($aContentEvent) || !is_array($aContentEvent))
             return '';
         
         $oModule = BxDolModule::getInstance($aContentEvent['module_name']);
         if ($oModule){
             if (isset($oModule->_oConfig->CNF['OBJECT_PRIVACY_VIEW'])){
                 $oPrivacy = BxDolPrivacy::getObjectInstance($oModule->_oConfig->CNF['OBJECT_PRIVACY_VIEW']);
                 if (!$oPrivacy->check($aContentEvent['content_id']))
                     return '';
             }
             $aRv = $oModule->serviceGetNotificationsPost(array('object_id' => $aContentEvent['content_id']));
             $aRv['lang_key'] = '_bx_channels_ntfs_txt_subobject_added';
             $aRv['channel_url'] = $this->serviceGetLink($aContentEvent['cnl_id']);
             return $aRv;
         }
         
         return '';
    }
}

/** @} */
