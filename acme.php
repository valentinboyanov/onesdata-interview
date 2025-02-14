<?php 

declare(strict_types=1);

readonly class Product
{
    public function __construct(
        public string $id,
        public string $name,
        public float $cost,
    ) {}

    public function purchasedByCustomers(array $orders): array
    {
        $customers = [];

        foreach ($orders as $order) {
            if ($order->hasProduct($this->id)) {
                $customers[] = $order->customer;
            }
        }

        return array_unique($customers);
    }
}

readonly class Customer
{
    public function __construct(
        public string $id,
        public string $firstname,
        public string $lastname,
    ) {}

    public function totalSpent($orderTotals): float
    {
        $totals = [];

        foreach ($orderTotals as $order) {
            if ($order->belongsToCustomer($this->id)) {
                $totals[] = $order->total;
            }
        }

        return array_sum($totals);
    }
}

readonly class Order
{
    public function __construct(
        public string $id,
        public string $customer,
        public string $products,
    ) {}

    public function cost(array $products): float
    {
        $costs = [];

        $productIds = explode(' ', $this->products);

        foreach ($productIds as $id) {
            foreach ($products as $product) {
                if ($product->id === $id) {
                    $costs[] = $product->cost;
                    break;
                }
            }
        }

        return array_sum($costs);
    }

    public function hasProduct($id): bool
    {
        $productIds = explode(' ', $this->products);

        return in_array($id, $productIds);
    }
}

readonly class OrderTotal
{
    public function __construct(
        public Order $order,
        public float $total,
    ) {}

    public function belongsToCustomer($id): bool
    {
        return $this->order->customer === $id;
    }
}

readonly class PurchasedProduct
{
    public function __construct(
        public string $id,
        public string $customers,
    ) {}
}

readonly class CustomerSpending
{
    public function __construct(
        public string $id,
        public string $firstname,
        public string $lastname,
        public float $totalSpent,
    ) {}
}

function csv_to_rows(string $filepath): iterable
{
    if (($handle = fopen($filepath, 'r')) !== false) {

        $header = fgetcsv($handle, escape: "");
        $columns = count($header);

        while (($row = fgetcsv($handle, escape: "")) !== false) {
            $result = [];

            for ($i = 0; $i < $columns; $i++) {
                $result[$header[$i]] = $row[$i];
            }

            yield $result;
        }

        fclose($handle);
    }
}

function to_products(iterable $rows): array
{
    $result = [];

    foreach($rows as $row) {
        $result[] = new Product(
            id: $row['id'],
            name: $row['name'],
            cost: (float)$row['cost'],
        );
    }

    return $result;
}

function to_orders(iterable $rows): array
{
    $result = [];

    foreach($rows as $row) {
        $result[] = new Order(
            id: $row['id'],
            customer: $row['customer'],
            products: $row['products'],
        );
    }

    return $result;
}

function to_customers(iterable $rows): array
{
    $result = [];

    foreach($rows as $row) {
        $result[] = new Customer(
            id: $row['id'],
            firstname: $row['firstname'],
            lastname: $row['lastname'],
        );
    }

    return $result;
}

function total_order_cost(array $orders, array $products): array
{
    $result = [];

    foreach ($orders as $order) {
        $result[] = new OrderTotal(
            order: $order,
            total: $order->cost($products),
        );
    }

    return $result;
}

function purchased_products_by_customer(array $products, array $orders): array
{
    $result = [];

    foreach ($products as $product) {
        $result[] = new PurchasedProduct(
            id: $product->id,
            customers: implode(" ", $product->purchasedByCustomers($orders)),
        );
    }

    return $result;
}

function ranked_customers(array $customers, array $orderTotals): array
{
    $result = [];

    foreach($customers as $customer) {
        $result[] = new CustomerSpending(
            id: $customer->id,
            firstname: $customer->firstname,
            lastname: $customer->lastname,
            totalSpent: $customer->totalSpent($orderTotals)
        );
    }

    usort($result, function($a, $b) {
        return $b->totalSpent <=> $a->totalSpent;
    });

    return $result;
}

function test_csv_to_products(): void
{
    $products = to_products(
        rows: csv_to_rows(filepath: 'tasks_input/products.csv')
    );

    assert(count($products) === 6);

    foreach ($products as $product) {
        assert($product instanceof Product);
    }

    // id,name,cost
    // 0,screwdriver,2.981163654411736
    $product = $products[0];

    assert($product->id === "0");
    assert($product->name === "screwdriver");
    assert($product->cost === 2.981163654411736);
}

function test_csv_to_orders(): void
{
    $orders = to_orders(
        rows: csv_to_rows(filepath: 'tasks_input/orders.csv')
    );

    assert(count($orders) === 50);

    foreach ($orders as $order) {
        assert($order instanceof Order);
    }

    // id,customer,products
    // 0,0,1 0 1 0
    $order = $orders[0];

    assert($order->id === "0");
    assert($order->customer === "0");
    assert($order->products === "1 0 1 0");
}

function test_csv_to_customers(): void
{
    $customers = to_customers(
        rows: csv_to_rows(filepath: 'tasks_input/customers.csv')
    );

    assert(count($customers) === 60);

    foreach ($customers as $customer) {
        assert($customer instanceof Customer);
    }

    // id,firstname,lastname
    // 0,John,Maxwell
    $customer = $customers[0];

    assert($customer->id === "0");
    assert($customer->firstname === "John");
    assert($customer->lastname === "Maxwell");
}

function test_total_order_cost(): void
{
    $products = to_products(
        rows: csv_to_rows(filepath: 'tasks_input/products.csv')
    );

    $orders = to_orders(
        rows: csv_to_rows(filepath: 'tasks_input/orders.csv')
    );

    $orderTotals = total_order_cost(
        orders: $orders, 
        products: $products
    );

    assert(count($orderTotals) === 50);

    foreach ($orderTotals as $order) {
        assert($order instanceof OrderTotal);
    }

    $orderTotal = $orderTotals[0];

    assert($orderTotal instanceof OrderTotal);
    assert($orderTotal->order->id === "0");

    // This string casting is due to float precision problems in PHP.
    // When casting a float number to string it removes some of the digits,
    // after the point. I don't know yet why this happens.
    assert((string)$orderTotal->total === (string)18.943120182823662);
}

function test_purchased_products_by_customer(): void
{
    $products = to_products(
        rows: csv_to_rows(filepath: 'tasks_input/products.csv')
    );

    $orders = to_orders(
        rows: csv_to_rows(filepath: 'tasks_input/orders.csv')
    );

    $purchasedProducts = purchased_products_by_customer(
        products: $products,
        orders: $orders,
    );

    assert(count($purchasedProducts) === 6);

    foreach ($purchasedProducts as $product) {
        assert($product instanceof PurchasedProduct);
    }

    $product = $purchasedProducts[0];
    assert($product->id === "0");
    assert($product->customers === "0 22 20 28 40 32 5 45 37 38 6 44 50 24 54 59 15 21 34 19 47 48 46 10 17 29");
}

function test_ranked_customers(): void
{
    $products = to_products(
        rows: csv_to_rows(filepath: 'tasks_input/products.csv')
    );

    $orders = to_orders(
        rows: csv_to_rows(filepath: 'tasks_input/orders.csv')
    );

    $orderTotals = total_order_cost(
        orders: $orders, 
        products: $products
    );

    $customers = to_customers(
        rows: csv_to_rows(filepath: 'tasks_input/customers.csv')
    );

    $rankedCustomers = ranked_customers(
        customers: $customers, 
        orderTotals: $orderTotals,
    );

    assert(count($rankedCustomers) === 60);

    foreach ($rankedCustomers as $customer) {
        assert($customer instanceof CustomerSpending);
    }

    $customer = $rankedCustomers[0];

    assert($customer->id === "34");
    assert($customer->firstname === "Samuel");
    assert($customer->lastname === "Lavoisier");

    // This string casting is due to float precision problems in PHP.
    // When casting a float number to string it removes some of the digits,
    // after the point. I don't know yet why this happens.
    assert((string)$customer->totalSpent === (string)145.2543359308485);
}
