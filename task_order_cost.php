<?php 

declare(strict_types=1);

require_once 'acme.php';

$orders = to_orders(
    rows: csv_to_rows(filepath: getenv(name: "ORDERS_CSV"))
);

$products = to_products(
    rows: csv_to_rows(filepath: getenv(name: "PRODUCTS_CSV"))
);

$orderTotals = total_order_cost(
    orders: $orders, 
    products: $products
);

$out = fopen('php://output', 'w');

//CSV header
fputcsv($out, ["id","euros",]);

foreach ($orderTotals as $order) {
    $row = [$order->order->id, $order->total];
    fputcsv($out, $row);
}

fclose($out);
