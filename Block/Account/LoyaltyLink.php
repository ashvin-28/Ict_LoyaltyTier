<?php

namespace Ict\LoyaltyTier\Block\Account;

use Ict\LoyaltyTier\Model\Config;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;

class LoyaltyLink extends Current
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->config = $config;
    }

    /**
     * Get configured account navigation label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->config->getFrontendLabel();
    }

    /**
     * Render link when module is enabled.
     *
     * @return string
     */
    // phpcs:ignore MEQP2.PHP.ProtectedClassMember.FoundProtected
    protected function _toHtml()
    {
        if (!$this->config->isEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
