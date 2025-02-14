<?php 

declare(strict_types=1);

require_once 'acme.php';

$orders = to_orders(
    rows: csv_to_rows(filepath: getenv(name: "ORDERS_CSV"))
);

$products = to_products(
    rows: csv_to_rows(filepath: getenv(name: "PRODUCTS_CSV"))
);

$purchasedProducts = purchased_products_by_customer(
    products: $products,
    orders: $orders,
);

$out = fopen('php://output', 'w');

//CSV header
fputcsv($out, ["id","customer_ids",], escape: "");

foreach ($purchasedProducts as $product) {
    $row = [$product->id, $product->customers];
    fputcsv($out, $row, escape: "");
}

fclose($out);
