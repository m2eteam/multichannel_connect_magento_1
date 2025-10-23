<?php
class M2E_MultichannelConnect_Model_Order_Tax_ProductTaxRate
{
    /** @var float */
    private $taxAmount;

    /** @var float */
    private $totalPrice;

    /** @var bool */
    private $isEnabledRoundingOfValue;

    /**
     * @param float $taxAmount
     * @param float $totalPrice
     * @param bool $isEnabledRoundingOfValue
     */
    public function __construct(
        $taxAmount,
        $totalPrice,
        $isEnabledRoundingOfValue
    ) {
        $this->taxAmount = $taxAmount;
        $this->totalPrice = $totalPrice;
        $this->isEnabledRoundingOfValue = $isEnabledRoundingOfValue;
    }

    /**
     * @return float|int
     */
    public function getValue()
    {
        $rate = $this->getCalculatedValue();

        if ($rate === 0) {
            return $rate;
        }

        return $this->isEnabledRoundingOfValue
            ? $this->getRoundedRate($rate)
            : round($rate, 4);
    }

    /**
     * @return float|int
     */
    public function getNotRoundedValue()
    {
        $rate = $this->getCalculatedValue();

        return $rate === 0 ? $rate : round($rate, 4);
    }

    /**
     * @return float|int
     */
    private function getCalculatedValue()
    {
        if ($this->taxAmount <= 0) {
            return 0;
        }

        return ($this->taxAmount / $this->totalPrice) * 100;
    }

    /**
     * @return bool
     */
    public function isEnabledRoundingOfValue()
    {
        return $this->isEnabledRoundingOfValue;
    }

    /**
     * @param $rate
     *
     * @return float
     */
    private function getRoundedRate($rate)
    {
        $decimalPart = $rate - floor($rate);

        if ($decimalPart === 0.5) {
            $rate = round($rate, 2);
        } else {
            $rate = round($rate);
        }

        return $rate;
    }
}
