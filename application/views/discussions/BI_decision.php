<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Business Intelligence</title>

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
        <h1>Decision Making</h1>
        <p>Understanding the importance and process of decision making in business intelligence</p>
    </header>

    <div class="content mt-4 mb-5">

        <h1>Importance of Decision Making</h1>

        <p>
            Decision making is one of the most important activities in organizations of all kinds.
            It determines the success or failure of organizations and directly impacts performance.
        </p>

        <h2>Why Decision Making is Difficult</h2>
        <ul>
            <li>Internal and external factors make decision making complex</li>
            <li>Rewards for good decisions can be high</li>
            <li>Losses from wrong decisions can also be very high</li>
            <li>Many decisions are not simple and require different approaches</li>
        </ul>

        <!-- <h2>Types of Organizational Decisions</h2>
        <p>De Smet et al. (2017) classify organizational decisions into four groups:</p>
        <ul>
            <li><strong>Big-bet, high-risk decisions</strong></li>
            <li><strong>Cross-cutting decisions</strong> – repetitive but high risk and require group work</li>
            <li><strong>Ad hoc decisions</strong> – arise episodically</li>
            <li><strong>Delegated decisions</strong> – made by individuals or small groups</li>
        </ul> -->

        <div class="alert alert-info">
            <strong>Note:</strong> It is essential to understand the nature of decision making before choosing the appropriate approach. For more details, see De Smet et al. (2017).
        </div>

        <h2>Modern Business Environment</h2>
        <p>
            Modern business is full of uncertainties and rapid changes. Decision makers must handle
            ever-increasing and changing data to make effective decisions.
        </p>

        <p>
            This book focuses on technologies that help decision makers analyze data and support
            decision making processes.
        </p>

        <hr>

        <h1>Decision-Making Process</h1>

        <h2>Introduction</h2>
        <p>
            Decision making used to be viewed as an art, relying mainly on managers’ experience,
            intuition, creativity, and judgment. However, modern research emphasizes that
            <strong>methodical, analytical decision making</strong> produces better outcomes than
            relying solely on interpersonal skills.
        </p>

        <h2>From Art to Science</h2>
        <ul>
            <li>Managers used <strong>trial and error</strong></li>
            <li>Decisions were based on <strong>intuition and experience</strong></li>
            <li>Decision styles varied from person to person</li>
        </ul>
        <p>
            Today, decision making is viewed as <strong>analytical and systematic</strong>, requiring
            managers to use <strong>data-driven approaches</strong>. Persistence and analytical thinking
            are more valuable than charisma.
        </p>

        <h2>Role of Analytics in Decision Making</h2>
        <p>Modern businesses use analytics to:</p>
        <ul>
            <li><strong>Describe</strong> what is happening (reporting)</li>
            <li><strong>Predict</strong> what will happen (forecasting)</li>
            <li><strong>Decide</strong> what actions to take (decision support)</li>
        </ul>
        <p>
            This process requires collecting large amounts of data and analyzing it using advanced tools
            and technologies.
        </p>

        <h2>Growth of Data</h2>
        <p>
            Data volume is rapidly increasing. The amount of data <strong>doubles every two years</strong>,
            and organizations must manage this growth efficiently.
        </p>

        <!-- <h2>Evolution of Computer Systems</h2>
        <p>
            Computer systems have expanded from payroll and bookkeeping to:
        </p>
        <ul>
            <li>Automated factory management</li>
            <li>Evaluating mergers and acquisitions</li>
            <li>Complex managerial decision-making</li>
        </ul> -->

        <h2>Modern BI Tools</h2>
        <p>Managers rely on tools such as:</p>
        <ul>
            <li>Data Warehousing</li>
            <li>Data Mining</li>
            <li>OLAP (Online Analytical Processing)</li>
            <li>Dashboards</li>
            <li>Cloud-based systems</li>
        </ul>

        <h2>Role of Information Systems</h2>
        <p>
            Modern management depends on high-speed networked systems (wired or wireless) with real-time
            data access, including mobile integration.
        </p>

        <div class="alert alert-info">
            <strong>Most important managerial task:</strong> Making decisions.
        </div>

        <h2>Automation of Decision Making</h2>
        <p>
            Many decisions are now automated using AI, machine learning, and rule-based systems,
            reducing the need for human intervention in routine decisions.
        </p>
        <hr>
        <h1>Technologies for Data Analysis and Decision Support</h1>

        <hr>

        <h2>Growth in Technology</h2>
        <p>
            Beyond improvements in hardware, software, and networks, several technology developments
            have supported the growth of decision support and analytics.
        </p>

        <h3>Group Communication and Collaboration</h3>
        <p>
            Many decisions are made by groups in different locations. Collaboration tools and smartphones
            enable easy communication, especially during events like the COVID-19 pandemic.
            This reduces travel costs and improves collaboration across supply chains.
        </p>

        <h3>Improved Data Management</h3>
        <p>
            Complex decisions often require data from different databases across organizations.
            Modern systems can store, search, transmit, and manage data quickly, securely, and efficiently,
            even when it includes text, audio, graphics, and video.
        </p>

        <h3>Managing Giant Data Warehouses and Big Data</h3>
        <p>
            Large data warehouses contain massive data volumes. Technologies such as parallel computing,
            Hadoop/Spark, and cloud-based systems help organize, search, and mine these data stores.
            Costs of storage and data mining are rapidly declining.
        </p>

        <h3>Analytical Support</h3>
        <p>
            More data and advanced analysis tools allow organizations to:
        </p>
        <ul>
            <li>Evaluate many alternatives</li>
            <li>Improve forecasts</li>
            <li>Perform quick risk analysis</li>
            <li>Collect expert views remotely</li>
            <li>Run complex simulations and scenarios</li>
        </ul>

        <h3>Overcoming Cognitive Limits</h3>
        <p>
            According to Simon (1977), human minds have limited ability to process and store information.
            Computer systems help overcome these limits by quickly accessing and processing vast information.
        </p>

        <h3>Knowledge Management</h3>
        <p>
            Organizations have collected huge amounts of information from operations, customers, employees,
            and stakeholders. Knowledge management systems (KMS) help support decision making.
            Text analytics can generate value from these knowledge stores.
        </p>

        <h3>Anywhere, Anytime Support</h3>
        <p>
            Mobile technologies enable managers to access information anytime and anywhere.
            This has dramatically increased the speed of decision-making and changed expectations for both
            businesses and consumers.
        </p>

        <h3>Innovation and Artificial Intelligence</h3>
        <p>
            AI supports innovation in decision making by improving many steps of the decision process.
            AI combined with analytics creates synergy in making better decisions.
        </p>

        <a class="btn alert-primary btn-block mb-3" href="<?=  base_url('./uploads/discussions/1.2-1.3.pdf') ?>" download="1.2-1.3.pdf" src="<?=  base_url('./uploads/discussions/1.2-1.3.pdf') ?>"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Chapter 1.2-1.3 Business Intelligence, Analytics, Data Science, and AI A Managerial Perspective (5th Edition) </a>


    </div>

</body>

</html>