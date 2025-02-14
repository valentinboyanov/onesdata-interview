# Overview

The main goal is me and [@j-plou](https://github.com/j-plou) beeing able to compare our solutions for learning purposes.

Secondary goals:
- Avoid using third party libraries as [league/csv](https://github.com/thephpleague/csv), [symfony/console](https://github.com/symfony/console) or [phpunit/phpunit](https://github.com/sebastianbergmann/phpunit/). This should force me to understand better the basic concepts by implementing the functionality by myself.
- Learn modern PHP: [smknstd/modern-php-cheatsheet](https://github.com/smknstd/modern-php-cheatsheet), https://stitcher.io/, https://php.watch/


## Prerequisites

- PHP 8

## Solution

Parts:
- [tasks_input](tasks_input): hosts the company's `.csv` files used to perform the tasks
- [tasks_output](tasks_output): hosts the `.csv` files resulted from the execution of each task
- [acme.php](acme.php): hosts the data and operations that represent the problem
- [acme_tests.php](acme_tests.php): find and run unit tests
- `task_*.php`: responsable to handle each task

### Task 1

> Right now the `orders.csv` doesn't have total order cost information.
>
> We need to use the data in these files to emit a `order_prices.csv` file with the following columns:
> * `id` the numeric id of the order
> * `euros` the total cost of the order

Run:

```
PRODUCTS_CSV="tasks_input/products.csv" \
ORDERS_CSV="tasks_input/orders.csv" \
php task_order_cost.php > tasks_output/order_prices.csv
```

### Task 2

> The marketing department wants to know which customers are interested in each product; they've asked for a `product_customers.csv` file that, for each product, gives the list of customers who have purchased this product:
> * `id` numeric product id
> * `customer_ids` a space-separated list of customer ids of the customers who have purchased this product

Run:

```
PRODUCTS_CSV="tasks_input/products.csv" \
ORDERS_CSV="tasks_input/orders.csv" \
php task_purchased_products.php > tasks_output/product_customers.csv
```

### Task 3

> To evaluate our customers, we need a `customer_ranking.csv` containing the following columns, ranked in descending order by total_euros:
> * `id` numeric id of the customer
> * `firstname` customer first name
> * `lastname` customer last name
> * `total_euros` total euros this customer has spent on products

Run:

```
PRODUCTS_CSV="tasks_input/products.csv" \
ORDERS_CSV="tasks_input/orders.csv" \
CUSTOMERS_CSV="tasks_input/customers.csv" \
php task_customers_ranking.php > tasks_output/customer_ranking.csv
```

## Tests

```
php acme_tests.php
```
