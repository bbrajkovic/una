<?php
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 */

class BxMarketUpdater extends BxDolStudioUpdater
{
    function __construct($aConfig)
	{
        parent::__construct($aConfig);
    }

	public function actionExecuteSql($sOperation)
    {
    	if($sOperation == 'install') {
    		if(!$this->oDb->isFieldExists('bx_market_products', 'score'))
        		$this->oDb->query("ALTER TABLE `bx_market_products` ADD `score` int(11) NOT NULL default '0' AFTER `votes`");
			if(!$this->oDb->isFieldExists('bx_market_products', 'sc_up'))
        		$this->oDb->query("ALTER TABLE `bx_market_products` ADD `sc_up` int(11) NOT NULL default '0' AFTER `score`");
			if(!$this->oDb->isFieldExists('bx_market_products', 'sc_down'))
        		$this->oDb->query("ALTER TABLE `bx_market_products` ADD `sc_down` int(11) NOT NULL default '0' AFTER `sc_up`");
		}

    	return parent::actionExecuteSql($sOperation);
    }
}
