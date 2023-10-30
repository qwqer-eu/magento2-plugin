<?php
namespace Qwqer\Express\Console;

use Magento\Framework\App\State;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Qwqer\Express\Model\Api\ParcelMachines;
use Qwqer\Express\Model\Api\GetOrdersList;
use Qwqer\Express\Model\Api\GetOrder;
use Qwqer\Express\Service\PublishOrder;

class Commands extends Command
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var ParcelMachines
     */
    private ParcelMachines $parcelMachines;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var GetOrdersList
     */
    private GetOrdersList $getOrdersList;

    /**
     * @var GetOrder
     */
    private GetOrder $getOrder;

    /**
     * @param State $state
     * @param ParcelMachines $parcelMachines
     * @param PublishOrder $publishOrder
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteFactory $quoteFactory
     * @param GetOrdersList $getOrdersList
     * @param GetOrder $getOrder
     * @param string|null $name
     */
    public function __construct(
        State $state,
        ParcelMachines $parcelMachines,
        PublishOrder $publishOrder,
        OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory,
        GetOrdersList $getOrdersList,
        GetOrder $getOrder,
        ?string $name = null
    ) {
        $this->state = $state;
        $this->parcelMachines = $parcelMachines;
        $this->publishOrder = $publishOrder;
        $this->orderRepository = $orderRepository;
        $this->quoteFactory = $quoteFactory;
        $this->getOrdersList = $getOrdersList;
        $this->getOrder = $getOrder;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('qwqer:do_request');
        $this->setDescription('Do Request to Qwqer');
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initState()
    {
        try {
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initState();

        $output->writeLn("Started");

        /**$orderId = 7;
        $order = $this->orderRepository->get($orderId);
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());
        $result = $this->publishOrder->execute($order, $quote);
        var_dump($result); die;*/
        //$response = $this->parcelMachines->getParcelDataByName('Rīgas Baltāsbaznīcas ielas MEGO pakomāts');
        //$orders = $this->getOrdersList->executeRequest();
        //$order = $this->getOrder->executeRequest(['order_id' => '2470']);
        $output->writeLn("Done");

        return 1;
    }

    /**
     * @param $orderId
     * @return false|\Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderById($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;
    }
}

