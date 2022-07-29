<?php
/**
 * Copyright © Landofcoder LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://landofcoder.com | info@landofcoder.com
 */

namespace Ves\All\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Ves\All\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Verify
 * @package Ves\All\Block\Adminhtml
 */
class Verify extends Template
{
    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $hash;

    const API_URL      = 'https://landofcoder.com/api/soap/?wsdl=1';
    const SITE_URL      = 'https://landofcoder.com';
    const API_USERNAME = 'checklicense';
    const API_PASSWORD = 'n2w3z2y0kc';

    protected $_key_path;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var array
     */
    private $_list_files = [];

    /**
     * Remove values from global post and store values locally
     * @var array()
     */
    protected $configFields = [
        'active' => '',
        'name' => '',
        'auth' => '',
        'ssl' => '',
        'smtphost' => '',
        'smtpport' => '',
        'username' => '',
        'password' => '',
        'set_reply_to' => '',
        'set_from' => '',
        'set_return_path' => '',
        'return_path_email' => '',
        'custom_from_email' => '',
        'email' => '',
        'from_email' => ''
    ];

    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected $curl;

    /**
     * EmailTest constructor.
     * @param Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Data $dataHelper
     * @param \Ves\All\Model\License $license
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\HTTP\Client\CurlFactory $curl
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        Data $dataHelper,
        \Ves\All\Model\License $license,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\HTTP\Client\CurlFactory $curl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_resource      = $resource;
        $this->_license       = $license;
        $this->_remoteAddress = $remoteAddress;
        $this->curl = $curl;
        $this->init();
    }

    /**
     * @param $id
     * @return $this
     */
    public function setStoreId($id)
    {
        $this->storeId = $id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param null $key
     * @return array|mixed|string
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->configFields;
        } elseif (!array_key_exists($key, $this->configFields)) {
            return '';
        } else {
            return $this->configFields[$key];
        }
    }

    /**
     * @param null $key
     * @param string $value
     * @return array|mixed|string
     */
    public function setConfig($key, $value = null)
    {
        if (array_key_exists($key, $this->configFields)) {
            $this->configFields[$key] = $value;
        }

        return $this;
    }

    /**
     * Load default config if config is lock using "bin/magento config:set"
     */
    public function loadDefaultConfig()
    {
        $request = $this->getRequest();
        $formPostArray = (array) $request->getPost();

        $fields = array_keys($this->configFields);
        foreach ($fields as $field) {
            if (!array_key_exists($field, $formPostArray)) {
                $this->setConfig($field, $this->_dataHelper->getConfig($field), $this->getStoreId());
            } else {
                $this->setConfig($field, $request->getPost($field));
            }
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function init()
    {
        $request = $this->getRequest();
        $this->setStoreId($request->getParam('store', null));

        $this->loadDefaultConfig();

        $this->hash = time() . '.' . rand(300000, 900000);
    }

    /**
     * get list license files
     *
     * @return array
     */
    public function getListLicenseFiles()
    {
        if(!$this->_list_files) {
            $path = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/Ves/');
            $files = glob($path . '*/*/license.xml');
            $path2 = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('vendor/Ves/');
            $files2 = glob($path2 . '*/*/license.xml');
            $path3 = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('vendor/venustheme/');
            $files3 = glob($path3 . '*/*/license.xml');
            $path4 = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('vendor/ves/');
            $files4 = glob($path4 . '*/*/license.xml');

            $path5 = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('vendor/magento2-modules/');
            $files5 = glob($path5 . '*/*/license.xml');


            if(is_array($files) && $files) {
                $this->_list_files = array_merge($this->_list_files, $files);
            }
            if(is_array($files2) && $files2) {
                $this->_list_files = array_merge($this->_list_files, $files2);
            }
            if(is_array($files3) && $files3) {
                $this->_list_files = array_merge($this->_list_files, $files3);
            }
            if(is_array($files4) && $files4) {
                $this->_list_files = array_merge($this->_list_files, $files4);
            }
            if(is_array($files5) && $files5) {
                $this->_list_files = array_merge($this->_list_files, $files5);
            }
        }
        return $this->_list_files;
    }

    public function getListModules()
    {
        $files = $this->getListLicenseFiles();
        $extensions = [];
        $email = $html = '';
        foreach ($files as $file) {
            $xmlObj = new \Magento\Framework\Simplexml\Config($file);
            $xmlData = $xmlObj->getNode();
            $sku = $xmlData->code;
            $name = $xmlData->name;
            if($email=='' && (string)($xmlData->email)){
                $email = $xmlData->email;
            }
            $_product = [];
            $_product['extension_name'] = (string)$name;
            $_product['purl']           = $xmlData->item_url;
            $_product['item_title']     = $xmlData->item_title;
            $_product['version']        = $xmlData->version;
            $_product['sku']            = $sku;
            $_product['key']            = ($xmlData->key)?$xmlData->key:'';
            $_product['pimg']           = ($xmlData->pimg)?$xmlData->pimg:'';
            $_product['field_name']     = str_replace(['-','_',' '], [''], $_product['sku']);
            $extensions[$_product['field_name']] = $_product;
        }
        return $extensions;
    }

    public function getListModulesNeedVerify()
    {
        $request = $this->getRequest();
        $modules = $request->getParam('module');
        $listModules = $this->getListModules();
        $needVerifyModules = [];
        if ($modules && count($modules)) {

            foreach ($modules as $key => $value) {
                $foundModule = isset($listModules[$key])?$listModules[$key]:null;
                if ($foundModule && $value) {
                    $foundModule["license"] = $value;
                    $needVerifyModules[] = $foundModule;
                }
            }
        }
        return $needVerifyModules;
    }

    /**
     * @return array
     */
    public function verify()
    {
        $modules = $this->getListModulesNeedVerify();
        $count = $fail = $success = 0;
        if ($modules) {
            $licenseCollection = $this->_license->getCollection();
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                $this->_storeManager->getStore()->isCurrentlySecure()
                );
            $remoteAddress = $this->_remoteAddress->getRemoteAddress();
            $domain        = $this->getDomain($baseUrl);

            foreach ($modules as $_extension) {
                $response = $this->verifyLicense($_extension['license'],$_extension['sku'], $domain, $remoteAddress);

                $license = isset($response["license"])?$response["license"]:false;

                if (!is_array($license) && $license === 1) {
                    $license = [];
                    $license['is_valid'] = 0;
                }
                if ($license === true) {
                    $license = [];
                    $license['is_valid'] = 1;
                }

                foreach ($licenseCollection as $klience => $vlience) {
                    if($vlience->getData('extension_code') == $_extension['sku']){
                        $vlience->delete();
                    }
                }
                $licenseData = [];
                if(isset($_extension['sku'])){
                    $licenseData['extension_code'] = $_extension['sku'];
                }
                if(isset($_extension['name'])){
                    $licenseData['extension_name'] = $_extension['name'];
                }
                if(empty($license) || !$license['is_valid']){
                    $licenseData['status'] = 0;
                    $fail++;
                }else{
                    $licenseData['status'] = 1;
                    $success++;
                }
                $this->_license->setData($licenseData)->save();
                $count++;
            }
        }
        $result = $this->error();
        $result['has_error'] = false;
        $result['msg'] = __("Total: %1, Valid: %2, InValid: %3", $count, $success, $fail);
        return [$result];
    }

    public function validateServerLicenseSetting()
    {
        $result = $this->error();
        return  $result;
    }


    /**
     * Format error msg
     * @param string $s
     * @return string
     */
    public function formatErrorMsg($s)
    {
        return preg_replace(
            '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
            '<a href="$1" target="_blank">$1</a>',
            nl2br($s)
        );
    }

    /**
     * @param bool $hasError
     * @param string $msg
     * @return array
     */
    public function error($hasError = false, $msg = '')
    {
        return [
            'has_error' => (bool) $hasError,
            'msg' => (string) $msg
        ];
    }

    public function verifyLicense($license_key, $extension, $domain, $ip)
    {
        $url = self::getVerifyUrl();

        try {
            $params = [
                'license_key' => $license_key,
                'extension' => $extension,
                'domain' => $domain,
                'ip' => $ip
            ];
            $curl = $this->curl->create();
            //$curl->addHeader("Content-Type", "application/json");
            $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            $curl->setOption(CURLOPT_TIMEOUT, 0);
            $curl->post($url, $params);
            $response = $curl->getBody();
            if ($response) {
                return json_decode($response, true);
            }
        } catch (\Exception $e) {
            $response = null;
        }
        try{
            //Authentication rest API magento2, get access token
            $direct_url = $url."?license_key=".$license_key."&extension=".$extension.'&domain='.$domain.'&ip='.$ip;
            $response = @file_get_contents($direct_url);
            if (!$response) {
                $data = array("license_key"=>$license_key,"extension"=>$extension,"domain"=>$domain,"ip"=>$ip);
                $key_path = $this->getKeyPath();
                $crl = curl_init();
                curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, TRUE);
                curl_setopt($crl, CURLOPT_CAPATH, $key_path);
                curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($crl, CURLOPT_URL, $url);
                curl_setopt($crl, CURLOPT_HEADER, 0);
                curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($crl, CURLOPT_POST, 1);
                curl_setopt($crl, CURLOPT_POSTFIELDS, $data);
                $response = curl_exec($crl);

                if ($response) {
                }
                else {
                    echo 'An error has occurred: ' . curl_error($crl);
                    return[];
                }
                curl_close($crl);
            }
            return json_decode($response, true);
        }catch(\Exception $e) {
            //
        }
        return [];
    }

    public static function getListUrl()
    {
        $url = self::SITE_URL;
        return $url."/license/listproducts";
    }

    public static function getVerifyUrl()
    {
        $url = self::SITE_URL;
        return $url."/license/verify";
    }

    public function getKeyPath()
    {
        if(!$this->_key_path){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
            $base_url = $directory->getRoot();
            $this->_key_path = $base_url."/veslicense/cacert.pem";
        }
        return $this->_key_path;
    }

    public function getDomain($domain)
    {
        $domain = strtolower($domain);
        $domain = str_replace(['www.','WWW.','https://','http://','https','http'], [''], $domain);
        if($this->endsWith($domain, '/')){
            $domain = substr_replace($domain ,"",-1);
        }
        return $domain;
    }

    public function endsWith($haystack, $needle)
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
