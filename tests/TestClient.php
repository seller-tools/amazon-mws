<?php

class TestClient extends TestCase
{

    public function test_connection_with_MWS()
    {
        $bool = $this->getClient()->validateCredentials();
        $this->assertTrue($bool);

        $corruptedCredentials = $this->getCredentials();
        $corruptedCredentials['MWSAuthToken'] = "SOME_RANDOM_STRING";
        $bool = $this->setClient($corruptedCredentials)->validateCredentials();

        $this->assertFalse($bool);
    }

    public function test_get_matching_products()
    {
        $asins = ['B00X4MDXOQ', 'B01HJ0VN40', 'B0711DGW5V', 'B00NLKAVL4', 'B010OMOSVK'];

        $products = $this->handleThrottling(function($asins)  {
            return $this->getClient()->GetMatchingProducts($asins);
        }, [$asins]);

        $this->assertNotEmpty($products['found'], 'Should get 5 products!');
        $this->assertEmpty($products['not_found'], 'Should get 0 products!');
        $this->assertEquals(2, count($products));
        $this->assertEquals(5, count($products['found']));


        $products = $this->handleThrottling(function($asins)  {
            return $this->getClient()->GetMatchingProducts($asins);
        }, [['B00X4MDXOQ', 'B00X4MDXOQ', 'B00X4MDXOQ', 'asd']]);

        $this->assertEquals(1, count($products['found']));
        $this->assertEquals(1, count($products['not_found']));
        $this->assertEquals('asd', $products['not_found'][0]);

        // test max asins
        $asins = ['B00X4MDXOQ', 'B01HJ0VN40', 'B0711DGW5V', 'B00NLKAVL4', 'B010OMOSVK',
            '_B00X4MDXOQ', '_B01HJ0VN40', '-B0711DGW5V', 'aB00NLKAVL4', 'asdB010OMOSVK', 'asdB010OMOSaVK'];


        $exception = null;
        try {
            $products = $this->handleThrottling(function ($asins) {
                return $this->getClient()->GetMatchingProducts($asins);
            }, [$asins]);
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'Exception should be trown!');

        $exception = null;
        try {
            $products = $this->handleThrottling(function ($asins) {
                return $this->getClient()->GetMatchingProducts($asins);
            }, [['++++)']]);
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'Exception should be trown!');

    }

    public function test_get_matching_products_with_merge()
    {
        $products = [
            'B00X4MDXOQ' => [
                'description' => "lorem ipsum ",
                'seller-sku' => "SKU123-213-NEW",
            ],
            'B01HJ0VN40' => [
                'description' => "lorem ipsum 2",
                'seller-sku' => "SKU3-OLD",
            ]
        ];
        $products = $this->handleThrottling(function($asins, $merge)  {
            return $this->getClient()->GetMatchingProducts($asins, $merge);
        }, [$products, true]);

        $this->assertEquals(2, count($products['found']), 'Should find 2 products!');
        $product = end($products['found']);
        $this->assertArrayHasKey('description', $product, 'Product should have a description!');
        $this->assertArrayHasKey('seller-sku', $product, 'Product should have a seller-sku!!');
    }

    public function test_get_matching_products_for_id()
    {
        $asins = ['B00X4MDXOQ', 'B01HJ0VN40', 'B0711DGW5V', 'B00NLKAVL4', 'B010OMOSVK'];

        $products = $this->handleThrottling(function($asins)  {
            return $this->getClient()->GetMatchingProductForId($asins);
        }, [$asins]);

        $this->assertNotEmpty($products['found'], 'Should get 5 products!');
        $this->assertEmpty($products['not_found'], 'Should get 0 products!');
        $this->assertEquals(2, count($products));
        $this->assertEquals(5, count($products['found']));


        $products = $this->handleThrottling(function($asins)  {
            return $this->getClient()->GetMatchingProductForId($asins);
        }, [['B00X4MDXOQ', 'B00X4MDXOQ', 'B00X4MDXOQ', 'asd']]);

        $this->assertEquals(1, count($products['found']));
        $this->assertEquals(1, count($products['not_found']));
        $this->assertEquals('asd', $products['not_found'][0]);


        $products = $this->handleThrottling(function($asins, $type)  {
            return $this->getClient()->GetMatchingProductForId($asins, $type);
        }, [['642872934924', '616913954806'], 'UPC']);

        $this->assertEquals(1, count($products['found']));
        $this->assertEquals(1, count($products['not_found']));
    }

    public function handleThrottling(callable $callback, array $params)
    {
        try {
            return call_user_func_array($callback, $params);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'throttle') !== false) {
                return $this->handleThrottling($callback, $params);
            }
            throw $e;
        }
    }
}