<?php

/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
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
 * @copyright Copyright (c) 2016 Total Internet Group B.V. (http://www.tig.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\Buckaroo\Block\Adminhtml\Sales\Order\Creditmemo\Create;

class RoundingWarning extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    protected $configProviderFactory;

    /**
     * RoundingWarning constructor.
     *
     * @param \Magento\Framework\Registry                       $registry
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderFactory
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->configProviderFactory = $configProviderFactory;
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->registry->registry('current_creditmemo');
    }

    /**
     * @return bool
     * @throws \LogicException
     * @throws \TIG\Buckaroo\Exception
     */
    protected function shouldShowWarning()
    {
        $creditmemo = $this->getCreditmemo();

        /**
         * The warning should only be shown before the refund has been processed.
         */
        if (!$creditmemo->canRefund()) {
            return false;
        }

        /**
         * The warning should only be shown for invoice-based refunds.
         */
        if (!$creditmemo->getInvoice()) {
            return false;
        }

        /**
         * The warning should only be shown when the order's currency is different from the store's base currency.
         */
        if ($creditmemo->getBaseCurrencyCode() == $creditmemo->getOrderCurrencyCode()) {
            return false;
        }

        /**
         * The warning should only be shown for Buckaroo payment methods.
         */
        $payment = $creditmemo->getOrder()->getPayment();
        $paymentMethod = $payment->getMethod();
        if (strpos($paymentMethod, 'tig_buckaroo') === false) {
            return false;
        }

        /**
         * @var \TIG\Buckaroo\Model\Method\AbstractMethod $paymentMethodInstance
         */
        $paymentMethodInstance = $payment->getMethodInstance();

        /**
         * Check if a config provider exists for the pament method used.
         */
        if (!$this->configProviderFactory->has($paymentMethodInstance->buckarooPaymentMethodCode)) {
            return false;
        }

        /**
         * The warning should only be shown if the order's currency is supported by the payment method used.
         */
        $configProvider = $this->configProviderFactory->get($paymentMethodInstance->buckarooPaymentMethodCode);
        if (!in_array($creditmemo->getOrderCurrencyCode(), $configProvider->getAllowedCurrencies())) {
            return false;
        }

        return true;
    }

    //@codingStandardsIgnoreStart
    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->shouldShowWarning()) {
            return '';
        }

        return parent::_toHtml();
    }
    //@codingStandardsIgnoreEnd
}
