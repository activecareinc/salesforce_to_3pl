<?php

namespace ThreePlCentral\Order;

use DateTime;
use ThreePlCentral\ThreePlCentral;
use ThreePlCentral\RequestFactory;
use ThreePlCentral\Exception;

class OrderRepository
{
    public static function findOrders(ThreePlCentral $threepl, DateTime $beginDate, DateTime $endDate)
    {
        $request = RequestFactory::create(
            $threepl,
            'POST',
            'http://www.JOI.com/schemas/ViaSub.WMS/FindOrders'
        );

        $request->setTemplate(__DIR__ . '/../Request/findOrders.xml');

        $response = $request->fetch([
            'BeginDate' => $beginDate->format('Y-m-d'),
            'EndDate' => $endDate->format('Y-m-d')
        ]);

        $result = $response->json();
        if (!is_array($result)) {
            $result = [$result];
        }

        $finalOrders = [];
        foreach ($result as $item) {
            $entity = new OrderEntity();
            foreach ($item as $key => $value) {
                $method = "set{$key}";
                if (is_string($value) && method_exists($entity, $method)) {
                    call_user_func([$entity, $method], $value);
                }
            }
            $finalOrders[] = $entity;
        }

        return $finalOrders;
    }
    
    /**
     * createOrder
     * @param ThreePlCentral $threepl
     * @param array $param
     * 
     * return object
     */
    public static function createOrder(ThreePlCentral $threepl, $param) {
    	// validate parameters
    	if (strlen($param['order_ref_number']) < 1) {
    		throw new Exception("Invalid order_ref_number passed. Must be string with value.");
    	}
    	
    	if (strlen($param['customer']) < 1) {
    		throw new Exception("Invalid customer passed. Must be string with value.");
    	}
    	
    	if (strlen($param['ship_to_name']) < 1) {
    		throw new Exception("Invalid ship_to_name passed. Must be string with value.");
    	}
    	
    	$request = RequestFactory::create(
    			$threepl,
    			'POST',
    			'http://www.JOI.com/schemas/ViaSub.WMS/CreateOrders'
    	);
    	
    	$request->setTemplate(__DIR__ . '/../Request/createOrder.xml');
    	
    	$response = $request->fetch([
    		'ReferenceNum' => $param['reference_num'],
    		'Name' => $param['ship_to'],
    		'CustomerName' => $param['customer']
    	]);
    	
    	$result = $response->json();
    	
    	return $result;
    }
}
