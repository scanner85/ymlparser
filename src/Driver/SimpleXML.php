<?php

namespace YMLParser\Driver;

class SimpleXML implements DriverInterface
{
    /**
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * @var string
     */
    private $filename;

    /**
     * Gets categories.
     *
     * @return arry Array of \YMLParser\Node\Category instances or empty array
     */
    public function getCategories()
    {
        $returnArr = [];

        foreach ($this->xml->shop->categories->category as $category):

            $arr = array_merge(
                ['value' => (string) $category],
                $this->getElementAttributes($category));

        $returnArr[] = new \YMLParser\Node\Category($arr);

        endforeach;

        return $returnArr;
    }

    /**
     * Gets offers.
     *
     * @param \Closure $filter Anonymous function
     *
     * @return array Array of \YMLParser\Node\Offer instances or empty array
     */
    public function getOffers(\Closure $filter = null)
    {
        foreach ($this->xml->shop->offers->offer as $offer):

            $arr = $this->getElementAttributes($offer);
        $arr['params'] = $this->parseParamsFromElement($offer);

        foreach ($offer->children() as $element):
                $name = mb_strtolower($element->getName());

        if ($name != 'param'):
                    $arr[$name] = (string) $element;
        endif;

        endforeach;

        $returnValue = new \YMLParser\Node\Category($arr);

        if (!is_null($filter)):
                if ($filter($returnValue)):
                    yield $returnValue;
        endif; else:
                yield $returnValue;
        endif;

        endforeach;
    }

    /**
     * Gets currencies.
     *
     * @return array
     */
    public function getCurrencies()
    {
        return [];
    }

    /**
     * Gets amount of offers.
     *
     * @return int
     */
    public function countOffers(\Closure $filter = null)
    {
        return 0;
    }

    /**
     * Opens filename.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function open($filename)
    {
        $this->filename = $filename;
        $this->xml = simplexml_load_file($filename);

        return (bool) $this->xml;
    }

    /**
     * Gets element params.
     *
     * @param \SimpleXMLElement $offer
     *
     * @return array
     */
    private function parseParamsFromElement(\SimpleXMLElement $offer)
    {
        $returnArr = [];

        foreach ($offer->children() as $element):

            if (mb_strtolower($element->getName()) == 'param'):
                $returnArr[] = array_merge(
                            ['value' => (string) $element],
                            $this->getElementAttributes($element)
                    );

        endif;

        endforeach;

        return $returnArr;
    }

    /**
     * Gets lement attributes.
     *
     * @param \SimpleXMLElement $element
     *
     * @return array
     */
    private function getElementAttributes(\SimpleXMLElement $element)
    {
        $returnArr = [];

        foreach ($element->attributes() as $attrName => $attrValue):
            $returnArr[strtolower($attrName)] = (string) $attrValue;
        endforeach;

        return $returnArr;
    }
}