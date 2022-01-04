<?php
namespace WeltPixel\GoogleTagManager\Controller\Adminhtml\Items;

/**
 * Class \WeltPixel\GoogleTagManager\Controller\Adminhtml\Items\Create
 */
class Create extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \WeltPixel\GoogleTagManager\Model\Api
     */
    protected $apiModel = null;

    /**
     * @var \WeltPixel\GoogleTagManager\Model\JsonGenerator
     */
    protected $jsonGenerator;

    /**
     * Version constructor.
     *
     * @param \WeltPixel\GoogleTagManager\Model\Api $apiModel
     * @param \WeltPixel\GoogleTagManager\Model\JsonGenerator $jsonGenerator
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \WeltPixel\GoogleTagManager\Model\Api $apiModel,
        \WeltPixel\GoogleTagManager\Model\JsonGenerator $jsonGenerator,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->apiModel = $apiModel;
        $this->jsonGenerator = $jsonGenerator;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $msg = $this->_validateParams($params);
        $apiOptions = ['variables', 'triggers', 'tags'];

        if (!count($msg)) {
            foreach ($apiOptions as $option) {
                try {
                    $result = $this->apiModel->
                    createItem(
                        $option,
                        $params['account_id'],
                        $params['container_id'],
                        $params['ua_tracking_id'],
                        $params['ip_anonymization'],
                        $params['display_advertising']
                    );
                    $msg = array_merge($msg, $result);
                } catch (\Exception $ex) {
                    $msg[] = $ex->getMessage();
                }
            }
        }

        if (!count($msg)) {
            $msg[] = __('Nothing was done, items were already created.');
        }


        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($msg);
        return $resultJson;
    }


    /**
     * @param $params
     * @return array
     */
    protected function _validateParams($params) {
        $accountId      = $params['account_id'];
        $containerId    = $params['container_id'];
        $uaTrackingId   = $params['ua_tracking_id'];

        $msg = [];

        if (!strlen(trim($accountId))) {
            $msg[] = __('Account Id must be specified');
        }

        if (!strlen(trim($containerId))) {
            $msg[] = __('Container Id must be specified');
        }

        if (!strlen(trim($uaTrackingId))) {
            $msg[] = __('Universal Tracking Id must be specified');
        }

        return $msg;
    }

}
