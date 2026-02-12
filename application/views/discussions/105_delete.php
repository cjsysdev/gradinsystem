<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SQL DELETE Statement</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => hljs.highlightAll());
    </script>
</head>

<body>

    <header>
        <h1>SQL DELETE Statement</h1>
        <p>Safely removing rows from a table using conditions, constraints, and best practices</p>
    </header>

    <main class="container">

        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Explain what <code>DELETE</code> does and how it differs from <code>TRUNCATE</code> and <code>DROP</code>.</li>
                <li>Write <code>DELETE</code> statements with <code>WHERE</code> conditions to remove specific rows.</li>
                <li>Apply safety checks (preview with <code>SELECT</code>, transactions, backups) before deleting.</li>
                <li>Understand how foreign keys and cascading rules affect delete operations.</li>
                <li>Use deletion best practices, including “soft delete” when appropriate.</li>
            </ul>
        </div>

        <div class="section">
            <h2>Concept: What Does DELETE Do?</h2>
            <p>
                The <strong>SQL DELETE</strong> statement removes <strong>rows</strong> from a table.
                It does <strong>not</strong> remove the table itself—only the records that match your condition.
            </p>

            <div class="alert alert-warning">
                <strong>Warning:</strong> If you run <code>DELETE</code> without a <code>WHERE</code> clause, it can remove <em>all</em> rows in the table.
            </div>

            <h3>DELETE vs TRUNCATE vs DROP</h3>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Command</th>
                            <th>Removes</th>
                            <th>Can use WHERE?</th>
                            <th>Table remains?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>DELETE</code></td>
                            <td>Selected rows</td>
                            <td>✅ Yes</td>
                            <td>✅ Yes</td>
                        </tr>
                        <tr>
                            <td><code>TRUNCATE</code></td>
                            <td>All rows (fast)</td>
                            <td>❌ No</td>
                            <td>✅ Yes</td>
                        </tr>
                        <tr>
                            <td><code>DROP</code></td>
                            <td>Table structure + all data</td>
                            <td>❌ No</td>
                            <td>❌ No</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Basic Syntax</h2>
            <pre><code class="language-sql">DELETE FROM table_name
WHERE condition;</code></pre>

            <div class="alert alert-info">
                <strong>Best Practice:</strong> Always write the <code>WHERE</code> first in your mind:
                “What exactly am I deleting?”
            </div>
        </div>

        <div class="section">
            <h2>Scenario: Small Online Shop</h2>
            <p>
                You manage a small online shop database. Some records must be removed:
                cancelled orders, outdated temporary data, or duplicate entries.
            </p>

            <h3>Sample Tables</h3>
            <pre><code class="language-sql">-- Customers table
CREATE TABLE customers (
  customer_id INT PRIMARY KEY,
  full_name   VARCHAR(100),
  email       VARCHAR(120) UNIQUE
);

-- Orders table (linked to customers)
CREATE TABLE orders (
  order_id    INT PRIMARY KEY,
  customer_id INT,
  status      VARCHAR(20),
  order_date  DATE,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- Example: add a "soft delete" flag (optional pattern)
-- ALTER TABLE customers ADD COLUMN is_deleted TINYINT(1) DEFAULT 0;</code></pre>
        </div>

        <div class="section">
            <h2>Examples</h2>

            <h3>1) Delete a specific row by primary key</h3>
            <pre><code class="language-sql">DELETE FROM customers
WHERE customer_id = 101;</code></pre>

            <h3>2) Delete rows that match a condition</h3>
            <pre><code class="language-sql">DELETE FROM orders
WHERE status = 'CANCELLED';</code></pre>

            <h3>3) Delete within a date range</h3>
            <pre><code class="language-sql">DELETE FROM orders
WHERE order_date &lt; '2024-01-01'
  AND status = 'CANCELLED';</code></pre>

            <h3>4) Preview first (recommended)</h3>
            <p>Before deleting, run the same condition using <code>SELECT</code>:</p>
            <pre><code class="language-sql">SELECT *
FROM orders
WHERE order_date &lt; '2024-01-01'
  AND status = 'CANCELLED';</code></pre>

            <h3>5) Safe delete pattern with LIMIT (MySQL)</h3>
            <div class="alert alert-secondary">
                Use <code>LIMIT</code> when you expect only a small number of rows, so you don’t accidentally wipe many rows.
            </div>
            <pre><code class="language-sql">DELETE FROM orders
WHERE status = 'CANCELLED'
LIMIT 5;</code></pre>

            <h3>6) Delete using JOIN (MySQL syntax)</h3>
            <p>Delete cancelled orders of a specific customer email:</p>
            <pre><code class="language-sql">DELETE o
FROM orders o
JOIN customers c ON c.customer_id = o.customer_id
WHERE c.email = 'maria.santos@email.com'
  AND o.status = 'CANCELLED';</code></pre>
        </div>

        <div class="section">
            <h2>Foreign Keys and Cascading Deletes</h2>
            <p>
                When tables are related (parent-child), deleting a parent row may fail if child rows still exist
                (to protect data integrity). This depends on the foreign key rule.
            </p>

            <h3>Common outcomes</h3>
            <ul>
                <li><strong>RESTRICT / NO ACTION:</strong> You can’t delete the parent if children exist.</li>
                <li><strong>CASCADE:</strong> Deleting the parent automatically deletes matching child rows.</li>
                <li><strong>SET NULL:</strong> Child foreign key is set to NULL when the parent is deleted.</li>
            </ul>

            <pre><code class="language-sql">-- Example: cascade (be careful!)
CREATE TABLE order_items (
  item_id  INT PRIMARY KEY,
  order_id INT,
  product  VARCHAR(80),
  qty      INT,
  FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE
);</code></pre>

            <div class="alert alert-warning">
                <strong>Reminder:</strong> <code>ON DELETE CASCADE</code> is powerful—one delete can remove many dependent rows.
            </div>
        </div>

        <div class="section">
            <h2>Safety Checklist (Use This Every Time)</h2>
            <ol>
                <li><strong>Preview:</strong> Run a <code>SELECT</code> with the same <code>WHERE</code>.</li>
                <li><strong>Count rows:</strong> <code>SELECT COUNT(*)</code> to confirm expected affected rows.</li>
                <li><strong>Transaction (if supported):</strong> Delete then <code>ROLLBACK</code> if wrong.</li>
                <li><strong>Limit small deletes:</strong> Use <code>LIMIT</code> when applicable.</li>
                <li><strong>Backups:</strong> Know how to restore (export / dump) before mass deletes.</li>
            </ol>

            <pre><code class="language-sql">-- Transaction example (InnoDB)
START TRANSACTION;

DELETE FROM orders
WHERE status = 'CANCELLED'
  AND order_date &lt; '2024-01-01';

-- Check affected rows quickly (or preview before running DELETE)
-- If correct:
COMMIT;

-- If wrong:
-- ROLLBACK;</code></pre>
        </div>

        <div class="section">
            <h2>Soft Delete (When You Should Consider It)</h2>
            <p>
                Instead of permanently deleting, many systems mark records as “deleted” so they can be restored
                or audited later.
            </p>
            <pre><code class="language-sql">-- Soft delete pattern
UPDATE customers
SET is_deleted = 1
WHERE customer_id = 101;

-- Query active customers only
SELECT *
FROM customers
WHERE is_deleted = 0;</code></pre>
            <div class="alert alert-info">
                Soft delete is useful for <strong>audit trails</strong>, <strong>recoverability</strong>, and <strong>compliance</strong>.
                Hard delete is useful for removing truly invalid/test data or meeting data retention policies.
            </div>
        </div>

        <div class="section">
            <h2>Hands-on Lab Activity: “Delete Safely” Practice</h2>
            <p><strong>Goal:</strong> Practice deleting the correct rows while avoiding accidental mass deletion.</p>

            <h3>Task A: Setup</h3>
            <pre><code class="language-sql">CREATE DATABASE shop_db;
USE shop_db;

CREATE TABLE customers (
  customer_id INT PRIMARY KEY,
  full_name   VARCHAR(100),
  email       VARCHAR(120) UNIQUE
);

CREATE TABLE orders (
  order_id    INT PRIMARY KEY,
  customer_id INT,
  status      VARCHAR(20),
  order_date  DATE,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

INSERT INTO customers VALUES
(101,'Maria Santos','maria@email.com'),
(102,'Juan Dela Cruz','juan@email.com'),
(103,'Ana Reyes','ana@email.com');

INSERT INTO orders VALUES
(1,101,'CANCELLED','2023-10-01'),
(2,101,'PAID','2023-10-10'),
(3,102,'CANCELLED','2022-05-12'),
(4,103,'CANCELLED','2024-02-01'),
(5,103,'PAID','2024-02-05');</code></pre>

            <h3>Task B: Required Deletes (Answer with SQL Commands)</h3>
            <ol>
                <li>Delete only the order with <code>order_id = 3</code>.</li>
                <li>Delete all <code>CANCELLED</code> orders before <code>2024-01-01</code>.</li>
                <li>Delete cancelled orders of customer <code>101</code> only (do not delete paid orders).</li>
            </ol>

            <h3>Task C: Verification</h3>
            <p>After each delete, prove your result by running:</p>
            <pre><code class="language-sql">SELECT * FROM orders ORDER BY order_id;</code></pre>

            <h3>Challenge (Optional)</h3>
            <ul>
                <li>Add <code>LIMIT</code> to a delete and explain why it increases safety.</li>
                <li>Try deleting customer <code>101</code>. What error happens? Explain why (foreign key constraint).</li>
            </ul>
        </div>

        <div class="section">
            <h2>Quick Summary</h2>
            <ul>
                <li><code>DELETE</code> removes rows; use <code>WHERE</code> to target specific records.</li>
                <li>Preview with <code>SELECT</code> first to avoid mistakes.</li>
                <li>Foreign keys may block deletes unless rules allow it (e.g., CASCADE).</li>
                <li>For recoverability, consider soft delete (flagging instead of removing).</li>
            </ul>
        </div>

    </main>

    <footer class="text-center">
        <small>Prepared for SQL Fundamentals — DELETE Statement</small>
    </footer>

</body>

</html>