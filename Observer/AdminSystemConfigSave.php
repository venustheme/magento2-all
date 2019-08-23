<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_All
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\All\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AdminSystemConfigSave implements ObserverInterface
{
	protected $configWriter;

	public function __construct(
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter
		) {
        $this->configWriter = $configWriter;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$configData        = $observer->getConfigData();
        $request = $observer->getRequest();
        $groups = $request->getParam('groups');
        $section = $request->getParam("section");
        if( ($section && $section=="veslicense") && (!$configData || ($configData && isset($configData['groups']) && !$configData['groups']))){
            $groups = $request->getParam('groups');
            if($groups && isset($groups['general']) && $groups['general']){
                $modules = $groups['general']['fields'];
                if($modules){
                    foreach($modules as $key=>$item){
                        $module_license_key = isset($item['value'])?$item['value']:'';
                        if($module_license_key){
                            $module_license_key = is_array($module_license_key)?implode(",",$module_license_key):$module_license_key;
                            $this->configWriter->save('veslicense/general/'.$key,  $module_license_key, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                        }
                    }
                }
            }
        }
		
	}
}