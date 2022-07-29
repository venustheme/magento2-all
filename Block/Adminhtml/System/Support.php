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

namespace Ves\All\Block\Adminhtml\System;

use Magento\Framework\App\Filesystem\DirectoryList;
use Ves\All\Block\Adminhtml\System\ListLicense;

class Support extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    protected $_key_path;

    /**
     * [__construct description]
     * @param \Magento\Backend\Block\Template\Context              $context       
     * @param \Magento\Framework\App\ResourceConnection            $resource 
     * @param \Ves\All\Helper\Data                                 $helper        
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress 
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Ves\All\Helper\Data $helper,
        \Ves\All\Model\License $license,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
        )
    {
        parent::__construct($context);
        $this->_resource      = $resource;
        $this->_helper        = $helper;
        $this->_remoteAddress = $remoteAddress;
        $this->_license       = $license;
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        $html .= '<div id="ves-elist">';
        $html .=  '<h1>Please connect with us via support chanels bellow</h1>';
        $html .= '<div>
            <ul>
                <li><a href="https://landofcoder.ticksy.com/">Submit Ticket</a></li>
                <li>Send Email to: <a href="mailto:info@landofcoder.com">info@landofcoder.com</a></li>
                <li><a href="https://landofcoder.com/contacts">Contact Form</a></li>
            </ul>
        </div>';
        $html .= '</div>';
        
        return $this->_decorateRowHtml($element, $html);
    }

}