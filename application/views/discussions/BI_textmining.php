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

    <div class="content my-5">
        <div class="discussion-container">

            <h1 class="text-center mb-4">Text Analytics, Text Mining, and Natural Language Processing</h1>

            <p>
                Modern organizations generate massive amounts of unstructured text data from emails,
                social media, reviews, reports, chat logs, and documents. To extract value from this data,
                techniques such as <strong>Text Analytics</strong>, <strong>Text Mining</strong>, and
                <strong>Natural Language Processing (NLP)</strong> are used.
            </p>

            <!-- TEXT ANALYTICS -->
            <h3 class="section-title">Text Analytics Overview</h3>
            <p>
                <strong>Text Analytics</strong> refers to the process of converting unstructured text into
                structured data to uncover meaningful patterns, trends, and insights. It focuses on
                analyzing text to support decision-making and business intelligence.
            </p>

            <ul>
                <li>Identifies trends and sentiments</li>
                <li>Extracts key topics and keywords</li>
                <li>Supports data-driven decisions</li>
            </ul>

            <div class="example-box">
                <strong>Example:</strong><br>
                Analyzing customer reviews to determine whether feedback is positive, negative, or neutral.
            </div>

            <!-- TEXT MINING -->
            <h3 class="section-title">Text Mining Overview</h3>
            <p>
                <strong>Text Mining</strong> is a subset of data mining that focuses on discovering hidden
                patterns and relationships within large collections of text data. It combines techniques
                from statistics, machine learning, and linguistics.
            </p>

            <p>
                While text analytics focuses on interpretation and insights, text mining emphasizes
                <strong>pattern discovery</strong> and <strong>knowledge extraction</strong>.
            </p>

            <div class="example-box">
                <strong>Example:</strong><br>
                Mining thousands of research papers to discover emerging topics or frequently associated terms.
            </div>

            <!-- COMPARISON TABLE -->
            <h4 class="mt-4">Text Analytics vs Text Mining</h4>
            <table class="table table-bordered mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>Aspect</th>
                        <th>Text Analytics</th>
                        <th>Text Mining</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Focus</td>
                        <td>Insight and interpretation</td>
                        <td>Pattern discovery</td>
                    </tr>
                    <tr>
                        <td>Purpose</td>
                        <td>Decision support</td>
                        <td>Knowledge extraction</td>
                    </tr>
                    <tr>
                        <td>Output</td>
                        <td>Sentiment, trends, summaries</td>
                        <td>Hidden patterns, associations</td>
                    </tr>
                </tbody>
            </table>

            <!-- NLP -->
            <h3 class="section-title">Natural Language Processing (NLP)</h3>
            <p>
                <strong>Natural Language Processing (NLP)</strong> is a field of artificial intelligence that enables computers to understand, interpret, and generate human language. NLP serves as the foundation for both text analytics and text mining.
            </p>

            <p>
                NLP allows machines to process text similarly to how humans read and understand language.
            </p>

            <h5>Common NLP Tasks</h5>
            <ul>
                <li><strong>Tokenization:</strong> The foundational process of breaking a stream of text into smaller units called "tokens" (such as words, characters, or subwords) to make the text machine-readable. </li>
                <li><strong>Part-of-speech tagging:</strong> The task of labeling each word in a sentence with its corresponding grammatical category—such as noun, verb, or adjective—based on its definition and context.</li>
                <li><strong>Named Entity Recognition (NER):</strong> The identification and classification of key information (entities) in a text into predefined categories like names of people, organizations, locations, and dates. </li>
                <li><strong>Sentiment analysis:</strong> Often called opinion mining, this involves determining the emotional tone behind a body of text to understand if the attitude expressed is positive, negative, or neutral.</li>
                <li><strong>Language translation:</strong> The automated process of converting text or speech from one language to another while ensuring the original meaning and context remain intact.</li>
            </ul>

            <div class="example-box">
                <strong>Example:</strong><br>
                A chatbot understanding user questions and responding in natural language.
            </div>

            <!-- RELATIONSHIP -->
            <h3 class="section-title">Relationship Between the Three</h3>
            <p>
                These concepts are closely related and often work together:
            </p>

            <ul>
                <li><strong>NLP</strong> provides language understanding techniques</li>
                <li><strong>Text Mining</strong> discovers patterns using NLP outputs</li>
                <li><strong>Text Analytics</strong> transforms results into insights for decision-making</li>
            </ul>

            <div class="example-box">
                <strong>Real-World Scenario:</strong><br>
                Social media posts are processed using NLP → patterns are discovered using text mining →
                business insights are generated using text analytics.
            </div>

            <!-- APPLICATIONS -->
            <h3 class="section-title">Real-World Applications</h3>
            <ul>
                <li>Customer sentiment analysis</li>
                <li>Spam and fraud detection</li>
                <li>Chatbots and virtual assistants</li>
                <li>Healthcare document analysis</li>
                <li>Market and competitor analysis</li>
            </ul>

            <h1 class="text-center mb-4">
                Real-World Applications of Text Analytics, Text Mining, and NLP
            </h1>

            <p>
                Text analytics, text mining, and natural language processing (NLP) are widely used in
                various industries to extract meaningful insights from unstructured text data.
                Below are common real-world applications that demonstrate how these technologies
                support decision-making and automation.
            </p>

            <!-- CUSTOMER REVIEWS -->
            <h3 class="section-title">Customer Reviews and Online Ratings</h3>
            <p>
                E-commerce platforms analyze customer reviews to understand user satisfaction
                and product performance.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP interprets customer-written text</li>
                    <li>Text analytics classifies reviews as positive, negative, or neutral</li>
                    <li>Insights are summarized for business decisions</li>
                </ul>
                <strong>Outcome:</strong> Faster understanding of customer feedback.
            </div>

            <!-- SOCIAL MEDIA -->
            <h3 class="section-title">Social Media Sentiment Analysis</h3>
            <p>
                Organizations monitor social media platforms to track public opinion about
                brands, products, or events.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP processes informal language, hashtags, and emojis</li>
                    <li>Text mining identifies trending topics</li>
                    <li>Text analytics summarizes overall sentiment</li>
                </ul>
                <strong>Outcome:</strong> Early detection of issues and trends.
            </div>

            <!-- CHATBOTS -->
            <h3 class="section-title">Chatbots and Virtual Assistants</h3>
            <p>
                Chatbots use NLP to communicate with users in natural language.
            </p>
            <div class="example-box">
                <strong>How it works:</strong>
                <ul>
                    <li>NLP identifies user intent</li>
                    <li>System generates relevant responses</li>
                </ul>
                <strong>Outcome:</strong> 24/7 automated customer support.
            </div>

            <a class="btn alert-primary btn-block mb-3" href="<?= base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/BI_book.pdf')) . '#page=336' ?>" target="_blank"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Business Intelligence, Analytics, Data Science, and AI A Managerial Perspective (5th Edition) </a>

        </div>
    </div>

</body>

</html>