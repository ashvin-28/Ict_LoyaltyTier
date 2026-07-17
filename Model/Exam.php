<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ict\LoyaltyTier\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * CMS block model
 *
 * @method Block setStoreId(int $storeId)
 * @method int getStoreId()
 */
class Exam extends AbstractModel
{
    /**
     * Dependency Initilization.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Ict\LoyaltyTier\Model\ResourceModel\Exam::class);
    }
}
