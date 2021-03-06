<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaBaseView UNA Base Representation Classes
 * @{
 */

/**
 * @see BxDolVote
 */
class BxBaseVote extends BxDolVote
{
    protected static $_sTmplContentElementBlock;
    protected static $_sTmplContentElementInline;
    protected static $_sTmplContentDoVoteLikesLabel;

	protected $_bCssJsAdded;

    protected $_sJsObjName;
    protected $_sStylePrefix;

    protected $_aHtmlIds;

    protected $_aElementDefaults;

    protected $_sTmplNameLegend;
    protected $_sTmplNameByList;
    protected $_sTmplNameDoVoteStars;

    public function __construct($sSystem, $iId, $iInit = true, $oTemplate = false)
    {
        parent::__construct($sSystem, $iId, $iInit, $oTemplate);

        $this->_bCssJsAdded = false;

        $this->_sJsObjName = 'oVote' . bx_gen_method_name($sSystem, array('_' , '-')) . $iId;
        $this->_sStylePrefix = 'bx-vote';

        $sHtmlId = str_replace(array('_' , ' '), array('-', '-'), $sSystem) . '-' . $iId;
        $this->_aHtmlIds = array(
            'main_stars' => 'bx-vote-stars-' . $sHtmlId,
            'main_likes' => 'bx-vote-likes-' . $sHtmlId,
            'counter' => 'bx-vote-counter-' . $sHtmlId,
            'by_popup' => 'bx-vote-by-popup-' . $sHtmlId,
        	'legend_stars' => 'bx-vote-stars-legend-' . $sHtmlId,
        );

        $this->_aElementDefaults = array(
            'stars' => array(
                'show_do_vote_legend' => false,
                'show_counter' => true,
        		'show_counter_empty' => false,
        		'show_legend' => false
            ),
            'likes' => array(
                'show_do_vote_as_button' => false,
                'show_do_vote_as_button_small' => false,
                'show_do_vote_icon' => true,
                'show_do_vote_label' => false,
                'show_counter' => true,
            	'show_counter_empty' => false,
            	'show_legend' => false
            )
        );

        $this->_sTmplNameLegend = 'vote_legend.html';
        $this->_sTmplNameByList = 'vote_by_list.html';
        $this->_sTmplNameDoVoteStars = 'vote_do_vote_stars.html';

        if(empty(self::$_sTmplContentElementBlock))
            self::$_sTmplContentElementBlock = $this->_oTemplate->getHtml('vote_element_block.html');

        if(empty(self::$_sTmplContentElementInline))
            self::$_sTmplContentElementInline = $this->_oTemplate->getHtml('vote_element_inline.html');

        if(empty(self::$_sTmplContentDoVoteLikesLabel))
            self::$_sTmplContentDoVoteLikesLabel = $this->_oTemplate->getHtml('vote_do_vote_likes_label.html');
    }

    public function getJsObjectName()
    {
        return $this->_sJsObjName;
    }

    public function getJsScript($bDynamicMode = false)
    {
        $aParams = array(
            'sObjName' => $this->_sJsObjName,
            'sSystem' => $this->getSystemName(),
            'iAuthorId' => $this->_getAuthorId(),
            'iObjId' => $this->getId(),
            'iLikeMode' => $this->isLikeMode() ? 1 : 0,
            'sRootUrl' => BX_DOL_URL_ROOT,
            'sStylePrefix' => $this->_sStylePrefix,
            'aHtmlIds' => $this->_aHtmlIds
        );
        $sCode = "var " . $this->_sJsObjName . " = new BxDolVote(" . json_encode($aParams) . ");";

        return $this->_oTemplate->_wrapInTagJsCode($sCode);
    }

    public function getJsClick()
    {
        if(!$this->isLikeMode())
            return false;

        return $this->getJsObjectName() . '.vote(this, ' . $this->getMaxValue() . ')';
    }

    public function getJsClickCounter()
    {
        return $this->getJsObjectName() . '.toggleByPopup(this)';
    }

    public function getCounter($aParams = array())
    {
        $sJsObject = $this->getJsObjectName();

        $bShowEmpty = isset($aParams['show_counter_empty']) && $aParams['show_counter_empty'] == true;
        $bShowDoVoteAsButtonSmall = $this->_bLike && isset($aParams['show_do_vote_as_button_small']) && $aParams['show_do_vote_as_button_small'] == true;
        $bShowDoVoteAsButton = $this->_bLike && !$bShowDoVoteAsButtonSmall && isset($aParams['show_do_vote_as_button']) && $aParams['show_do_vote_as_button'] == true;

        $aVote = $this->_oQuery->getVote($this->getId());
        $sClass = $this->_sStylePrefix . '-counter';
        if($bShowDoVoteAsButtonSmall)
            $sClass .= ' bx-btn-small-height';
        if($bShowDoVoteAsButton)
            $sClass .= ' bx-btn-height';

        return $this->_oTemplate->parseLink('javascript:void(0)', $bShowEmpty || (int)$aVote['count'] > 0 ? $this->_getLabelCounter($aVote['count']) : '', array(
            'id' => $this->_aHtmlIds['counter'],
            'class' => $sClass,
            'title' => _t($this->_getTitleDoBy()),
            'onclick' => 'javascript:' . $this->getJsClickCounter() 
        ));
    }

    /**
     * Note. The Legend is available in Start based mode only.  
     */
    public function getLegend($aParams = array())
    {
        $sJsObject = $this->getJsObjectName();
        $iMinValue = $this->getMinValue();
        $iMaxValue = $this->getMaxValue();

        $aLegend = $this->_oQuery->getLegend($this->_iId);

        $aTmplVarsItems = array();
        for($i = $iMaxValue; $i >= $iMinValue; $i--) {
        	$aTmplVarsStars = $aTmplVarsSlider = array();

	        for($j = $iMinValue; $j <= $iMaxValue; $j++) {
	        	$aTmplVarsStars[] = array(
	                'style_prefix' => $this->_sStylePrefix,
	                'value' => $i
	            );
	
	            $aTmplVarsSlider[] = array(
	                'style_prefix' => $this->_sStylePrefix
	            );
	        }

	        $aTmplVarsItems[] = array(
	        	'style_prefix' => $this->_sStylePrefix,
	        	'value' => $i,
	        	'bx_repeat:stars' => $aTmplVarsStars,
	        	'bx_repeat:slider' => $aTmplVarsSlider,
	        	'label' => isset($aLegend[$i]['count']) ? (int)$aLegend[$i]['count'] : 0
	        );
        }

        return $this->_oTemplate->parseHtmlByName($this->_sTmplNameLegend, array(
        	'style_prefix'  => $this->_sStylePrefix,
        	'html_id' => $this->_aHtmlIds['legend_stars'],
        	'type' => $this->_sType,
        	'bx_repeat:items' => $aTmplVarsItems
        ));
    }

    public function getElementBlock($aParams = array())
    {
        $aParams['usage'] = BX_DOL_VOTE_USAGE_BLOCK;

        return $this->getElement($aParams);
    }

    public function getElementInline($aParams = array())
    {
        $aParams['usage'] = BX_DOL_VOTE_USAGE_INLINE;

        return $this->getElement($aParams);
    }

    public function getElement($aParams = array())
    {
    	$aParams = array_merge($this->_aElementDefaults[$this->_sType], $aParams);
    	$bDynamicMode = isset($aParams['dynamic_mode']) && $aParams['dynamic_mode'] === true;

        $bShowCounterEmpty = isset($aParams['show_counter_empty']) && $aParams['show_counter_empty'] == true;
        $bShowDoVoteAsButtonSmall = $this->_bLike && isset($aParams['show_do_vote_as_button_small']) && $aParams['show_do_vote_as_button_small'] == true;
        $bShowDoVoteAsButton = $this->_bLike && !$bShowDoVoteAsButtonSmall && isset($aParams['show_do_vote_as_button']) && $aParams['show_do_vote_as_button'] == true;

        $sMethodDoVote = '_getDoVote' . ucfirst($this->_sType);
        if(!method_exists($this, $sMethodDoVote))
            return '';

		$iObjectId = $this->getId();
		$iAuthorId = $this->_getAuthorId();
		$aVote = $this->_oQuery->getVote($iObjectId);
        $bCount = (int)$aVote['count'] != 0;

        $isAllowedVote = $this->isAllowedVote();
        $aParams['is_voted'] = $this->isPerformed($iObjectId, $iAuthorId) ? true : false;

        //--- Do Vote
        $bTmplVarsDoVote = $this->_isShowDoVote($aParams, $isAllowedVote, $bCount);
        $aTmplVarsDoVote = array();
        if($bTmplVarsDoVote)
        	$aTmplVarsDoVote = array(
				'style_prefix' => $this->_sStylePrefix,
				'do_vote' => $this->$sMethodDoVote($aParams, $isAllowedVote),
			);

        //--- Counter
        $bTmplVarsCounter = $this->_isShowCounter($aParams, $isAllowedVote, $bCount);
        $aTmplVarsCounter = array();
        if($bTmplVarsCounter)
        	$aTmplVarsCounter = array(
				'style_prefix' => $this->_sStylePrefix,
				'bx_if:show_hidden' => array(
					'condition' => !$bCount && !$bShowCounterEmpty,
					'content' => array()
				),
				'counter' => $this->getCounter($aParams)
        	);

		//--- Legend
		$bTmplVarsLegend = $this->_isShowLegend($aParams, $isAllowedVote, $bCount);
		$aTmplVarsLegend = array();
		if($bTmplVarsLegend)
			$aTmplVarsLegend = array(
				'legend' => $this->getLegend($aParams)
			);

		if(!$bTmplVarsDoVote && !$bTmplVarsCounter && !$bTmplVarsLegend)
			return '';

        $sTmplName = self::${'_sTmplContentElement' . bx_gen_method_name(!empty($aParams['usage']) ? $aParams['usage'] : BX_DOL_VOTE_USAGE_DEFAULT)};
        return $this->_oTemplate->parseHtmlByContent($sTmplName, array(
            'style_prefix' => $this->_sStylePrefix,
            'html_id' => $this->_aHtmlIds['main_' . $this->_sType],
            'class' => $this->_sStylePrefix . '-' . $this->_sType . ($bShowDoVoteAsButton ? '-button' : '') . ($bShowDoVoteAsButtonSmall ? '-button-small' : ''),
            'rate' => $aVote['rate'],
            'count' => $aVote['count'],
        	'bx_if:show_do_vote' => array(
        		'condition' => $bTmplVarsDoVote,
        		'content' => $aTmplVarsDoVote
        	),
        	'bx_if:show_counter' => array(
				'condition' => $bTmplVarsCounter,
				'content' => $aTmplVarsCounter
			),
            'bx_if:show_legend' => array(
            	'condition' => $bTmplVarsLegend,
            	'content' => $aTmplVarsLegend
            ),
            'script' => $this->getJsScript($bDynamicMode)
        ));
    }

    protected function _getDoVoteStars($aParams = array(), $isAllowedVote = true)
    {
        $sJsObject = $this->getJsObjectName();
        $iMinValue = $this->getMinValue();
        $iMaxValue = $this->getMaxValue();

        $aTmplVarsStars = $aTmplVarsLegend = $aTmplVarsSlider = $aTmplVarsButtons = array();
        for($i = $iMinValue; $i <= $iMaxValue; $i++) {
            $aTmplVarsStars[] = array(
                'style_prefix' => $this->_sStylePrefix,
                'value' => $i
            );

            $aTmplVarsLegend[] = array(
                'style_prefix' => $this->_sStylePrefix,
                'value' => $i
            );

            $aTmplVarsSlider[] = array(
                'style_prefix' => $this->_sStylePrefix
            );

            if($isAllowedVote)
	            $aTmplVarsButtons[] = array(
	                'style_prefix' => $this->_sStylePrefix,
	                'js_object' => $sJsObject,
	                'value' => $i
	            );
        }

        return $this->_oTemplate->parseHtmlByName($this->_sTmplNameDoVoteStars, array(
            'style_prefix' => $this->_sStylePrefix,
            'bx_repeat:stars' => $aTmplVarsStars,
            'bx_if:show_legend' => array(
                'condition' => isset($aParams['show_do_vote_legend']) && $aParams['show_do_vote_legend'] === true,
                'content' => array(
                    'style_prefix' => $this->_sStylePrefix,
                    'bx_repeat:legend' => $aTmplVarsLegend,
                )
            ),
            'bx_repeat:slider' => $aTmplVarsSlider,
            'bx_repeat:buttons' => $aTmplVarsButtons,
        ));
    }

    protected function _getDoVoteLikes($aParams = array(), $isAllowedVote = true)
    {
    	$bVoted = isset($aParams['is_voted']) && $aParams['is_voted'] === true;
        $bShowDoVoteAsButtonSmall = isset($aParams['show_do_vote_as_button_small']) && $aParams['show_do_vote_as_button_small'] == true;
        $bShowDoVoteAsButton = !$bShowDoVoteAsButtonSmall && isset($aParams['show_do_vote_as_button']) && $aParams['show_do_vote_as_button'] == true;
		$bDisabled = !$isAllowedVote || ($bVoted && !$this->isUndo());

        $sClass = '';
		if($bShowDoVoteAsButton)
			$sClass = 'bx-btn';
		else if ($bShowDoVoteAsButtonSmall)
			$sClass = 'bx-btn bx-btn-small';

		if($bDisabled)
			$sClass .= $bShowDoVoteAsButton || $bShowDoVoteAsButtonSmall ? ' bx-btn-disabled' : 'bx-vote-disabled';

        return $this->_oTemplate->parseLink('javascript:void(0)', $this->_getLabelDoLike($aParams), array(
            'class' => $this->_sStylePrefix . '-do-vote ' . $sClass,
            'title' => _t($this->_getTitleDoLike($bVoted)),
            'onclick' => !$bDisabled ? $this->getJsClick() : ''
        ));
    }

    protected function _getLabelCounter($iCount)
    {
        return _t('_vote_counter', $iCount);
    }

    protected function _getLabelDoLike($aParams = array())
    {
    	$bVoted = isset($aParams['is_voted']) && $aParams['is_voted'] === true;
        return $this->_oTemplate->parseHtmlByContent(self::$_sTmplContentDoVoteLikesLabel, array(
        	'bx_if:show_icon' => array(
        		'condition' => isset($aParams['show_do_vote_icon']) && $aParams['show_do_vote_icon'] == true,
        		'content' => array(
        			'name' => $this->_getIconDoLike($bVoted)
        		)
        	),
        	'bx_if:show_text' => array(
        		'condition' => isset($aParams['show_do_vote_label']) && $aParams['show_do_vote_label'] == true,
        		'content' => array(
        			'text' => _t($this->_getTitleDoLike($bVoted))
        		)
        	)
        ));
    }

    protected function _getVotedBy()
    {
        $aTmplUsers = array();

        $aUserIds = $this->_oQuery->getPerformedBy($this->getId());
        foreach($aUserIds as $iUserId) {
            list($sUserName, $sUserUrl, $sUserIcon, $sUserUnit) = $this->_getAuthorInfo($iUserId);
            $aTmplUsers[] = array(
                'style_prefix' => $this->_sStylePrefix,
                'user_unit' => $sUserUnit
            );
        }

        if(empty($aTmplUsers))
            $aTmplUsers = MsgBox(_t('_Empty'));

        return $this->_oTemplate->parseHtmlByName($this->_sTmplNameByList, array(
            'style_prefix' => $this->_sStylePrefix,
            'bx_repeat:list' => $aTmplUsers
        ));
    }

    protected function _isShowDoVote($aParams, $isAllowedVote, $bCount)
    {
        $bShowDoVote = !isset($aParams['show_do_vote']) || $aParams['show_do_vote'] == true;

        return $bShowDoVote && (!$this->_bLike || $isAllowedVote || $bCount);
    }

    protected function _isShowCounter($aParams, $isAllowedVote, $bCount)
    {
        $bShowCounter = isset($aParams['show_counter']) && $aParams['show_counter'] === true;

        return $bShowCounter && ($isAllowedVote || $bCount);
    }

    protected function _isShowLegend($aParams, $isAllowedVote, $bCount)
    {
        return !$this->_bLike && isset($aParams['show_legend']) && $aParams['show_legend'] === true;
    }
}

/** @} */
