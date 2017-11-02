<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\ConfigProvider\Method;

class PaymentGuarantee extends AbstractConfigProvider
{
    const XPATH_ALLOWED_CURRENCIES                    = 'buckaroo/tig_buckaroo_paymentguarantee/allowed_currencies';

    const XPATH_ALLOW_SPECIFIC                  = 'payment/tig_buckaroo_paymentguarantee/allowspecific';
    const XPATH_SPECIFIC_COUNTRY                = 'payment/tig_buckaroo_paymentguarantee/specificcountry';

    const XPATH_PAYMENTGUARANTEE_PAYMENT_FEE          = 'payment/tig_buckaroo_paymentguarantee/payment_fee';
    const XPATH_PAYMENTGUARANTEE_PAYMENT_FEE_LABEL    = 'payment/tig_buckaroo_paymentguarantee/payment_fee_label';
    const XPATH_PAYMENTGUARANTEE_ACTIVE               = 'payment/tig_buckaroo_paymentguarantee/active';
    const XPATH_PAYMENTGUARANTEE_ACTIVE_STATUS        = 'payment/tig_buckaroo_paymentguarantee/active_status';
    const XPATH_PAYMENTGUARANTEE_ORDER_STATUS_SUCCESS = 'payment/tig_buckaroo_paymentguarantee/order_status_success';
    const XPATH_PAYMENTGUARANTEE_ORDER_STATUS_FAILED  = 'payment/tig_buckaroo_paymentguarantee/order_status_failed';
    const XPATH_PAYMENTGUARANTEE_SEND_EMAIL           = 'payment/tig_buckaroo_paymentguarantee/send_email';
    const XPATH_PAYMENTGUARANTEE_PAYMENT_METHOD       = 'payment/tig_buckaroo_paymentguarantee/payment_method';

    /**
     * @var array
     */
    protected $allowedCurrencies = [
        'EUR'
    ];

    private $paymentNames = [
        '1' => 'transfer',
        '2' => 'ideal'
    ];

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->scopeConfig->getValue(static::XPATH_PAYMENTGUARANTEE_ACTIVE)) {
            return [];
        }

        $paymentFeeLabel = $this->getBuckarooPaymentFeeLabel(\TIG\Buckaroo\Model\Method\PaymentGuarantee::PAYMENT_METHOD_CODE);

        return [
            'payment' => [
                'buckaroo' => [
                    'paymentguarantee' => [
                        'paymentFeeLabel' => $paymentFeeLabel,
                        'allowedCurrencies' => $this->getAllowedCurrencies(),
                        'paymentMethod' => $this->getPaymentMethod()
                    ],
                    'response' => []
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getPaymentMethodToUse()
    {
        $paymentMethodConfiguredKeys =  array_combine($this->getPaymentMethod(), $this->getPaymentMethod()); //set value as key
        $paymentMethodConfigured = array_intersect_key($this->paymentNames, $paymentMethodConfiguredKeys);

        $paymentMethodString = implode(',', $paymentMethodConfigured);

        return $paymentMethodString;
    }

    /**
     * @return array
     */
    public function getPaymentMethod()
    {
        $paymentMethod = $this->scopeConfig->getValue(
            self::XPATH_PAYMENTGUARANTEE_PAYMENT_METHOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($paymentMethod)) {
            return array_keys($this->paymentNames);  // Default use both
        }

        $paymentMethodConfigured = explode(',', $paymentMethod);


        return $paymentMethodConfigured;
    }

    /**
     * @return mixed
     */
    public function getSendMail()
    {
        $sendMail = $this->scopeConfig->getValue(
            self::XPATH_PAYMENTGUARANTEE_SEND_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $sendMail ? 'true' : 'false';
    }

    /**
     * @return float
     */
    public function getPaymentFee()
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_PAYMENTGUARANTEE_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentFee ? $paymentFee : 0;
    }
}