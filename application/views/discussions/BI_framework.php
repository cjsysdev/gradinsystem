<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Intelligence Overview</title>

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
</head>

<body>

    <header>
        <h1>Business Intelligence (BI)</h1>
        <p>Concepts, architecture, and role of BI in modern decision making.</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Business Intelligence Fundamentals</h2>

        <p class="discussion-intro">
            Business Intelligence (BI) evolved from earlier decision support systems and enterprise
            information systems. Its primary goal is to transform data into insights that support
            faster, smarter, and more informed business decisions.
        </p>

        <hr>

        <h4>Evolution of Business Intelligence</h4>
        <p>
            Decision support concepts were implemented gradually under various names.
            As enterprise-wide systems matured, managers gained access to user-friendly reports
            that supported quick decision making. These systems were initially known as
            <b>Executive Information Systems (EIS)</b>.
        </p>
        <p>
            Over time, EIS platforms added visualization tools, alerts, and performance measurement
            features. By 2006, these capabilities were collectively recognized under the term
            <b>Business Intelligence (BI)</b>.
        </p>

        <div class="alert alert-info">
            <b>Key idea:</b> BI is the modern evolution of DSS and EIS technologies.
        </div>

        <hr>

        <h4>Definition of Business Intelligence</h4>
        <p>
            <b>Business Intelligence (BI)</b> is an umbrella term that includes architectures, tools,
            databases, analytical applications, and methodologies used to analyze business data.
        </p>
        <ul>
            <li>Provides interactive access to data</li>
            <li>Supports real-time or near real-time analysis</li>
            <li>Enables data manipulation and exploration</li>
            <li>Helps transform data into decisions and actions</li>
        </ul>

        <div class="alert alert-success">
            <b>Key idea:</b> BI turns data into information, decisions, and actions.
        </div>

        <hr>

        <h4>Architecture of BI</h4>
        <p>A typical BI system is composed of four major components:</p>
        <ul>
            <li><b>Data Warehouse (DW):</b> Stores integrated and historical data</li>
            <li><b>Business Analytics:</b> Tools for querying, reporting, and data mining</li>
            <li><b>Business Performance Management (BPM):</b> Monitors and evaluates performance</li>
            <li><b>User Interface:</b> Dashboards and visualization tools</li>
        </ul>

        <div class="alert alert-warning">
            <b>Key idea:</b> BI architecture separates data storage, analysis, and presentation.
        </div>

        <hr>

        <h4>Origins and Drivers of BI</h4>
        <p>
            Organizations invest in BI to better understand their data and improve decision making.
            Regulatory requirements, competitive pressure, and compressed business cycles have
            increased the demand for timely and accurate information.
        </p>
        <ul>
            <li>Regulatory compliance (e.g., Sarbanes-Oxley Act)</li>
            <li>Need for faster and better decisions</li>
            <li>Increased accountability and transparency</li>
        </ul>

        <div class="alert alert-info">
            <b>Key idea:</b> Managers need the right information at the right time.
        </div>

        <hr>

        <h4>Transaction Processing vs Analytic Processing</h4>
        <p>
            BI systems are designed for analysis, not for handling daily transactions.
            <b>Online Transaction Processing (OLTP)</b> systems manage routine business operations
            such as sales, deposits, and inventory updates.
        </p>
        <p>
            In contrast, <b>Online Analytical Processing (OLAP)</b> systems use data warehouses to
            analyze historical and current data for decision support.
        </p>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Aspect</th>
                    <th>OLTP</th>
                    <th>OLAP</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Purpose</td>
                    <td>Daily operations</td>
                    <td>Analysis and decision support</td>
                </tr>
                <tr>
                    <td>Data</td>
                    <td>Current, detailed</td>
                    <td>Historical, summarized</td>
                </tr>
                <tr>
                    <td>Users</td>
                    <td>Clerks, operators</td>
                    <td>Managers, analysts</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>Planning and Alignment with Business Strategy</h4>
        <p>
            BI initiatives must align with organizational strategy. BI is not just a technical
            project—it is a business transformation tool that promotes data-driven decision making.
        </p>
        <p>
            Many organizations establish a <b>BI Competency Center</b> to support best practices,
            collaboration, and alignment between business users and IT teams.
        </p>

        <div class="alert alert-success">
            <b>Key idea:</b> Successful BI starts with strategy alignment.
        </div>

        <hr>

        <h4>Real-Time and On-Demand BI</h4>
        <p>
            Modern BI increasingly supports real-time or near real-time access to data.
            Technologies such as sensors, RFID, and event-driven systems enable organizations
            to monitor operations as they happen.
        </p>
        <ul>
            <li>Real-time alerts and notifications</li>
            <li>Automated responses to business events</li>
            <li>Business Activity Management (BAM)</li>
        </ul>

        <div class="alert alert-warning">
            <b>Key idea:</b> Real-time BI enables agile and proactive decision making.
        </div>

        <hr>

        <h4>Developing or Acquiring BI Systems</h4>
        <p>
            Organizations can build, purchase, or lease BI solutions. Many vendors offer
            pre-built BI platforms that can be customized to business needs.
        </p>
        <p>
            The choice depends on cost, flexibility, expertise, and long-term strategy.
        </p>

        <hr>

        <h4>Security, Privacy, and Integration</h4>
        <p>
            BI systems often contain sensitive and strategic data. Ensuring data security and
            protecting privacy are critical requirements.
        </p>
        <p>
            BI solutions must also integrate with other systems such as ERP, CRM, and
            e-commerce platforms to deliver maximum value.
        </p>

        <div class="alert alert-danger">
            <b>Key idea:</b> Secure, integrated BI systems lead to better and safer insights.
        </div>

                <a class="btn alert-primary btn-block mb-3" href="<?= base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/BI_book.pdf')) . '#page=59' ?>" target="_blank"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Business Intelligence, Analytics, Data Science, and AI A Managerial Perspective (5th Edition) </a>

    </div>

</body>
</html>