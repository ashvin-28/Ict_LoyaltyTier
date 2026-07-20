<?php

namespace Ict\LoyaltyTier\Controller\Account;

use Ict\LoyaltyTier\Model\Config;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Config $config
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Show customer loyalty tier page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($this->config->getFrontendLabel());

        return $resultPage;
    }
}
