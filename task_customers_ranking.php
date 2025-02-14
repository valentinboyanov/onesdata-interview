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

$customers = to_customers(
    rows: csv_to_rows(filepath: getenv(name: "CUSTOMERS_CSV"))
);

$rankedCustomers = ranked_customers(
    customers: $customers, 
    orderTotals: $orderTotals,
);

$out = fopen('php://output', 'w');

//CSV header
fputcsv($out, ["id", "firstname", "lastname", "total_euros"], escape: "");

foreach ($rankedCustomers as $customer) {
    $row = [
        $customer->id, 
        $customer->firstname,
        $customer->lastname,
        $customer->totalSpent,
    ];
    fputcsv($out, $row, escape: "");
}

fclose($out);
