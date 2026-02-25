<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SQL JOIN Queries</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>

    <style>
        /* Optional: small helpers (safe even if discussion-style.css exists) */
        .note {
            background: #f8f9fa;
            border-left: 4px solid #ced4da;
            padding: .75rem 1rem;
            border-radius: .25rem;
        }

        .mini-title {
            font-weight: 700;
            margin-bottom: .25rem;
        }

        code {
            font-size: 0.95em;
        }
    </style>
</head>

<body>

    <header>
        <h1>SQL JOIN Queries</h1>
        <p>Combine rows from two or more tables using related columns</p>
    </header>

    <main class="container pb-4">

        <!-- Learning Objectives -->
        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Explain why JOINs are needed in relational databases.</li>
                <li>Differentiate INNER, LEFT, RIGHT, and FULL joins.</li>
                <li>Write JOIN queries using correct <code>ON</code> conditions and table aliases.</li>
                <li>Avoid common JOIN mistakes (wrong keys, duplicated rows, filtering in the wrong place).</li>
            </ul>
        </div>

        <!-- Prerequisites -->
        <div class="section">
            <h2>Prerequisites</h2>
            <ul>
                <li>Basic <code>SELECT</code>, <code>WHERE</code>, <code>ORDER BY</code></li>
                <li>Understanding of primary key (PK) and foreign key (FK)</li>
            </ul>
        </div>

        <!-- Core Idea -->
        <div class="section">
            <h2>Core Idea</h2>
            <p>
                A <strong>JOIN</strong> lets you retrieve related data stored across multiple normalized tables.
                Instead of repeating information in one big table, we connect tables through matching keys (usually PK ↔ FK).
            </p>

            <div class="note">
                <div class="mini-title">Rule of thumb</div>
                <div>
                    <strong>JOIN</strong> chooses <em>which rows match</em> (via <code>ON</code>) and
                    <strong>WHERE</strong> applies <em>filters after joining</em> (unless you intentionally filter before with a subquery).
                </div>
            </div>
        </div>

        <!-- Sample Schema -->
        <div class="section">
            <h2>Sample Schema (Used in Examples)</h2>
            <p>We will use a simple ordering system:</p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Key Columns</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>customers</strong></td>
                            <td><code>customer_id</code> (PK)</td>
                            <td>Stores customer info</td>
                        </tr>
                        <tr>
                            <td><strong>orders</strong></td>
                            <td><code>order_id</code> (PK), <code>customer_id</code> (FK)</td>
                            <td>Stores orders made by customers</td>
                        </tr>
                        <tr>
                            <td><strong>order_items</strong></td>
                            <td><code>order_item_id</code> (PK), <code>order_id</code> (FK), <code>product_id</code> (FK)</td>
                            <td>Stores items inside an order</td>
                        </tr>
                        <tr>
                            <td><strong>products</strong></td>
                            <td><code>product_id</code> (PK)</td>
                            <td>Stores product info</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mb-2"><strong>Quick relationship map:</strong></p>
            <ul>
                <li><code>customers (1) → (many) orders</code></li>
                <li><code>orders (1) → (many) order_items</code></li>
                <li><code>products (1) → (many) order_items</code></li>
            </ul>
        </div>

        <!-- JOIN Syntax -->
        <div class="section">
            <h2>Basic JOIN Syntax</h2>
            <pre><code class="language-sql">SELECT
  ...
FROM tableA AS a
JOIN tableB AS b
  ON a.key = b.key;</code></pre>

            <div class="note">
                <div class="mini-title">Aliases matter</div>
                <div>
                    Use short aliases (<code>c</code>, <code>o</code>, <code>oi</code>, <code>p</code>) to keep queries readable,
                    especially with 3+ tables.
                </div>
            </div>
        </div>

        <!-- INNER JOIN -->
        <div class="section">
            <h2>1) INNER JOIN</h2>
            <p>
                Returns only rows that have matching values in both tables.
                If there is no match, the row is excluded.
            </p>

            <pre><code class="language-sql">-- List orders with the customer name
SELECT
  o.order_id,
  o.order_date,
  c.customer_name
FROM orders AS o
INNER JOIN customers AS c
  ON o.customer_id = c.customer_id
ORDER BY o.order_date DESC;</code></pre>

            <div class="note">
                <div class="mini-title">When to use</div>
                <div>When you only want records that definitely have a related match (e.g., orders must have a customer).</div>
            </div>
        </div>

        <!-- LEFT JOIN -->
        <div class="section">
            <h2>2) LEFT JOIN (LEFT OUTER JOIN)</h2>
            <p>
                Returns <strong>all</strong> rows from the left table, and matching rows from the right table.
                If there is no match, right-side columns become <code>NULL</code>.
            </p>

            <pre><code class="language-sql">-- Show all customers and any orders they may have (including customers with no orders)
SELECT
  c.customer_id,
  c.customer_name,
  o.order_id,
  o.order_date
FROM customers AS c
LEFT JOIN orders AS o
  ON c.customer_id = o.customer_id
ORDER BY c.customer_name;</code></pre>

            <div class="note">
                <div class="mini-title">Common use</div>
                <div>Finding “missing” related data (e.g., customers with no orders).</div>
            </div>

            <pre><code class="language-sql">-- Customers with NO orders (classic LEFT JOIN + NULL check)
SELECT
  c.customer_id,
  c.customer_name
FROM customers AS c
LEFT JOIN orders AS o
  ON c.customer_id = o.customer_id
WHERE o.order_id IS NULL;</code></pre>
        </div>

        <!-- RIGHT JOIN -->
        <div class="section">
            <h2>3) RIGHT JOIN (RIGHT OUTER JOIN)</h2>
            <p>
                Returns all rows from the right table, and matching rows from the left table.
                In practice, many developers prefer rewriting this as a LEFT JOIN (for consistency).
            </p>

            <pre><code class="language-sql">-- Example (same result can usually be written as a LEFT JOIN by swapping tables)
SELECT
  c.customer_id,
  c.customer_name,
  o.order_id
FROM customers AS c
RIGHT JOIN orders AS o
  ON c.customer_id = o.customer_id;</code></pre>
        </div>

        <!-- FULL OUTER JOIN -->
        <div class="section">
            <h2>4) FULL OUTER JOIN</h2>
            <p>
                Returns all rows from both tables. Matches are combined; non-matches appear with <code>NULL</code> on the missing side.
            </p>

            <div class="note">
                <div class="mini-title">MySQL note</div>
                <div>
                    MySQL does <strong>not</strong> support <code>FULL OUTER JOIN</code> directly.
                    You can simulate it using <code>LEFT JOIN</code> + <code>UNION</code> with a <code>RIGHT JOIN</code>.
                </div>
            </div>

            <pre><code class="language-sql">-- FULL OUTER JOIN simulation in MySQL
SELECT
  c.customer_id,
  c.customer_name,
  o.order_id,
  o.order_date
FROM customers AS c
LEFT JOIN orders AS o
  ON c.customer_id = o.customer_id

UNION

SELECT
  c.customer_id,
  c.customer_name,
  o.order_id,
  o.order_date
FROM customers AS c
RIGHT JOIN orders AS o
  ON c.customer_id = o.customer_id;</code></pre>
        </div>

        <!-- JOIN 3+ Tables -->
        <div class="section">
            <h2>JOINing 3+ Tables (Most Real Queries)</h2>
            <p>
                When you join multiple tables, connect each table using the correct relationship path.
                (Avoid “guessing” keys.)
            </p>

            <pre><code class="language-sql">-- Show order details with product names and line totals
SELECT
  o.order_id,
  o.order_date,
  c.customer_name,
  p.product_name,
  oi.quantity,
  oi.unit_price,
  (oi.quantity * oi.unit_price) AS line_total
FROM orders AS o
INNER JOIN customers AS c
  ON o.customer_id = c.customer_id
INNER JOIN order_items AS oi
  ON o.order_id = oi.order_id
INNER JOIN products AS p
  ON oi.product_id = p.product_id
ORDER BY o.order_id;</code></pre>

            <div class="note">
                <div class="mini-title">Why duplicates happen</div>
                <div>
                    If one order has many items, the order row will appear many times—one per item.
                    That’s expected in detail reports.
                </div>
            </div>
        </div>

        <!-- Aggregation with JOIN -->
        <div class="section">
            <h2>Aggregation with JOIN (Totals per Customer / Order)</h2>
            <pre><code class="language-sql">-- Total sales per order
SELECT
  o.order_id,
  SUM(oi.quantity * oi.unit_price) AS order_total
FROM orders AS o
INNER JOIN order_items AS oi
  ON o.order_id = oi.order_id
GROUP BY o.order_id
ORDER BY order_total DESC;</code></pre>

            <pre><code class="language-sql">-- Total sales per customer (customers with no orders will not appear)
SELECT
  c.customer_id,
  c.customer_name,
  SUM(oi.quantity * oi.unit_price) AS total_spent
FROM customers AS c
INNER JOIN orders AS o
  ON c.customer_id = o.customer_id
INNER JOIN order_items AS oi
  ON o.order_id = oi.order_id
GROUP BY c.customer_id, c.customer_name
ORDER BY total_spent DESC;</code></pre>

            <pre><code class="language-sql">-- Total sales per customer (including customers with no orders -> shows NULL, use COALESCE to show 0)
SELECT
  c.customer_id,
  c.customer_name,
  COALESCE(SUM(oi.quantity * oi.unit_price), 0) AS total_spent
FROM customers AS c
LEFT JOIN orders AS o
  ON c.customer_id = o.customer_id
LEFT JOIN order_items AS oi
  ON o.order_id = oi.order_id
GROUP BY c.customer_id, c.customer_name
ORDER BY total_spent DESC;</code></pre>
        </div>

        <!-- Common Mistakes -->
        <div class="section">
            <h2>Common JOIN Mistakes (and Fixes)</h2>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Mistake</th>
                            <th>What happens</th>
                            <th>Fix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Joining on the wrong columns</td>
                            <td>Wrong matches / huge result sets</td>
                            <td>Follow PK ↔ FK relationships; verify keys</td>
                        </tr>
                        <tr>
                            <td>Forgetting the <code>ON</code> condition</td>
                            <td>Cross join (cartesian product) explosion</td>
                            <td>Always specify correct <code>ON</code></td>
                        </tr>
                        <tr>
                            <td>Filtering LEFT JOIN results in <code>WHERE</code> incorrectly</td>
                            <td>LEFT JOIN behaves like INNER JOIN</td>
                            <td>Move right-table filter into <code>ON</code> when needed</td>
                        </tr>
                        <tr>
                            <td>Selecting <code>*</code> with many tables</td>
                            <td>Confusing columns / duplicates</td>
                            <td>Select only needed columns with aliases</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <pre><code class="language-sql">-- Example: keeping LEFT JOIN behavior while filtering orders by year
SELECT
  c.customer_id,
  c.customer_name,
  o.order_id,
  o.order_date
FROM customers AS c
LEFT JOIN orders AS o
  ON c.customer_id = o.customer_id
  AND YEAR(o.order_date) = 2026;  -- filter placed in ON, not WHERE</code></pre>
        </div>

        <!-- Hands-on Lab -->
        <div class="section">
            <h2>Hands-on Lab Activity (JOIN Practice)</h2>
            <p><strong>Goal:</strong> Write JOIN queries using the schema above.</p>

            <ol>
                <li>
                    <strong>INNER JOIN:</strong> Display each order with the customer name.
                    <div class="note mt-2">
                        Output columns: <code>order_id</code>, <code>order_date</code>, <code>customer_name</code>
                    </div>
                </li>
                <li class="mt-2">
                    <strong>LEFT JOIN:</strong> List all customers, including those without orders.
                    <div class="note mt-2">
                        Output columns: <code>customer_id</code>, <code>customer_name</code>, <code>order_id</code>
                    </div>
                </li>
                <li class="mt-2">
                    <strong>3-table JOIN:</strong> Show order line items with product name.
                    <div class="note mt-2">
                        Output columns: <code>order_id</code>, <code>product_name</code>, <code>quantity</code>, <code>unit_price</code>, <code>line_total</code>
                    </div>
                </li>
                <li class="mt-2">
                    <strong>Aggregation:</strong> Compute total amount per order and sort highest to lowest.
                    <div class="note mt-2">
                        Output columns: <code>order_id</code>, <code>order_total</code>
                    </div>
                </li>
                <li class="mt-2">
                    <strong>Challenge:</strong> Find customers with no orders.
                    <div class="note mt-2">
                        Output columns: <code>customer_id</code>, <code>customer_name</code>
                    </div>
                </li>
            </ol>
        </div>

        <!-- Quick Check -->
        <div class="section">
            <h2>Quick Check (Exit Questions)</h2>
            <ol>
                <li>What is the difference between <code>INNER JOIN</code> and <code>LEFT JOIN</code>?</li>
                <li>Why does an order appear multiple times when joining with <code>order_items</code>?</li>
                <li>When should you filter in the <code>ON</code> clause instead of <code>WHERE</code>?</li>
            </ol>
        </div>

        <!-- Summary -->
        <div class="section">
            <h2>Summary</h2>
            <ul class="mb-0">
                <li><strong>INNER JOIN</strong> = only matching rows.</li>
                <li><strong>LEFT JOIN</strong> = keep all left rows, unmatched right becomes <code>NULL</code>.</li>
                <li>Multi-table JOINs follow relationships (PK ↔ FK) step-by-step.</li>
                <li>Aggregation + JOIN is used for totals and reports.</li>
            </ul>
        </div>

    </main>

    <footer class="text-center pb-4">
        <small>SQL JOIN Queries • Web Discussion Format (Bootstrap + Highlight.js)</small>
    </footer>

    <?php $this->load->view('web_to_image'); ?>

</body>

</html>