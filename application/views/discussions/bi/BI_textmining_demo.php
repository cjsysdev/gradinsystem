<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Mining</title>

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
        <h1>Text Mining</h1>
        <p>Understanding how unstructured text data can be transformed into valuable business intelligence insights.</p>
    </header>

    <div class="content mt-4 mb-5">

        <!-- Learning Objectives -->
        <section class="section">
            <h2>Learning Objectives</h2>
            <p>
                By the end of this lesson, students should be able to explain the concept of text mining,
                describe its process, and identify how it supports predictive and prescriptive analytics.
            </p>
            <ul>
                <li>Define text mining in the context of Business Intelligence.</li>
                <li>Differentiate structured and unstructured data.</li>
                <li>Explain the major stages in the text mining process.</li>
                <li>Understand common techniques such as tokenization and TF-IDF.</li>
                <li>Recognize practical applications and ethical issues.</li>
            </ul>
        </section>

        <!-- Introduction -->
        <section class="section">
            <h2>What is Text Mining?</h2>
            <p>
                Text mining (also called text analytics) is the process of extracting meaningful patterns,
                trends, and knowledge from textual data. Unlike traditional data stored in tables,
                text data is unstructured and does not follow a fixed format.
            </p>

            <p>
                In Business Intelligence, text mining allows organizations to analyze customer reviews,
                emails, social media posts, feedback forms, and reports to gain deeper insights
                beyond numerical data.
            </p>

            <div class="alert alert-info">
                Text mining converts human language into structured information that computers can analyze.
            </div>
        </section>

        <!-- Structured vs Unstructured -->
        <section class="section">
            <h2>Structured vs Unstructured Data</h2>
            <p>
                Data in organizations generally falls into two categories:
            </p>

            <ul>
                <li><strong>Structured Data:</strong> Organized into rows and columns (e.g., sales database).</li>
                <li><strong>Unstructured Data:</strong> Free-form text such as reviews, emails, and comments.</li>
            </ul>

            <p>
                Studies show that more than 80% of organizational data is unstructured,
                making text mining a critical skill in modern analytics.
            </p>
        </section>

        <!-- Process -->
        <section class="section">
            <h2>Text Mining Process</h2>
            <p>
                The text mining workflow follows a structured pipeline to transform raw text
                into actionable insights:
            </p>

            <ol>
                <li><strong>Text Collection</strong> – Gathering documents from various sources.</li>
                <li><strong>Preprocessing</strong> – Cleaning and preparing text.</li>
                <li><strong>Feature Extraction</strong> – Converting text into numeric format.</li>
                <li><strong>Modeling</strong> – Applying algorithms such as classification or clustering.</li>
                <li><strong>Evaluation</strong> – Measuring accuracy and interpreting results.</li>
            </ol>
        </section>

        <!-- Preprocessing -->
        <section class="section">
            <h2>Text Preprocessing</h2>
            <p>
                Raw text often contains noise such as punctuation, capital letters,
                and common words that do not add meaning. Preprocessing improves model accuracy.
            </p>

            <ul>
                <li><strong>Tokenization:</strong> Splitting text into words.</li>
                <li><strong>Stop-word Removal:</strong> Removing common words (e.g., "the", "is").</li>
                <li><strong>Stemming:</strong> Reducing words to root form.</li>
                <li><strong>Lemmatization:</strong> Converting words to dictionary base form.</li>
            </ul>

            <pre><code class="language-python">
text = "Students are learning text mining."
tokens = text.lower().split()
print(tokens)
</code></pre>
        </section>

        <!-- Representation -->
        <section class="section">
            <h2>Converting Text into Numbers</h2>
            <p>
                Since computers cannot directly interpret text, it must be converted into
                numerical representations before modeling.
            </p>

            <ul>
                <li><strong>Bag of Words (BoW):</strong> Counts word occurrences.</li>
                <li><strong>TF-IDF (Term Frequency-Inverse Document Frequency) :</strong> Measures importance of words in documents.</li>
                <li><strong>Word Embeddings:</strong> Represents words as vectors capturing meaning.</li>
            </ul>

            <div class="alert alert-warning">
                The quality of feature representation greatly affects predictive model performance.
            </div>
        </section>

        <!-- Word Embeddings -->
        <!-- <section class="section">
            <h2>Word Embeddings</h2>

            <p>
                Traditional methods like Bag of Words and TF-IDF treat words as independent tokens.
                However, they do not capture the meaning or relationships between words.
                Word embeddings solve this limitation by representing words as dense numerical vectors
                that encode semantic meaning.
            </p>

            <p>
                In word embeddings, words with similar meanings are positioned close to each other
                in a multi-dimensional vector space. This allows models to understand context and similarity.
            </p>

            <div class="alert alert-info">
                Word embeddings transform words into coordinates in a semantic space.
                Similar words have similar vector representations.
            </div>

            <h5>Example Concept</h5>
            <p>
                If we map words into a 2D space (simplified example):
            </p>

            <pre><code class="language-python">
King   → [0.8, 0.6]
Queen  → [0.82, 0.58]
Man    → [0.5, 0.4]
Woman  → [0.52, 0.38]
</code></pre>

            <p>
                Notice that "King" is closer to "Queen" than to "Man".
                The distance between vectors represents similarity.
            </p>

            <h5>Popular Word Embedding Models</h5>
            <ul>
                <li><strong>Word2Vec</strong> – Learns word relationships from context.</li>
                <li><strong>GloVe</strong> – Uses global word co-occurrence statistics.</li>
                <li><strong>FastText</strong> – Handles subword information (good for rare words).</li>
            </ul>

            <div class="alert alert-warning">
                Unlike TF-IDF, embeddings capture semantic similarity, not just frequency.
            </div>
        </section> -->

        <section class="section">
            <h2>Document Embeddings using Multilingual SBERT</h2>

            <p>
                In modern text mining, document embeddings allow computers to represent
                entire sentences or documents as numerical vectors that capture meaning.
                One popular model is <strong>Sentence-BERT (SBERT)</strong>.
                When using <strong>Multilingual SBERT</strong>, the model can generate
                embeddings across multiple languages while preserving semantic similarity.
            </p>

            <p>
                This means documents written in different languages can still be considered
                similar if they express the same idea.
            </p>

            <div class="alert alert-info">
                Multilingual SBERT focuses on <strong>meaning (semantics)</strong>,
                not just matching keywords.
            </div>

            <h4>Why Use Multilingual SBERT?</h4>
            <ul>
                <li>Captures contextual meaning instead of simple word frequency.</li>
                <li>Works across multiple languages (useful for multilingual datasets).</li>
                <li>Improves semantic search and document similarity tasks.</li>
                <li>Handles synonyms and different writing styles better than TF-IDF.</li>
            </ul>

            <h4>Typical Workflow</h4>
            <ol>
                <li>Collect text documents or sentences.</li>
                <li>Generate embeddings using multilingual SBERT.</li>
                <li>Convert each document into a vector representation.</li>
                <li>Compute similarity using cosine similarity.</li>
                <li>Retrieve nearest neighbors (most similar documents).</li>
            </ol>

            <h4>Similarity Measurement</h4>
            <p>
                After embedding generation, similarity between documents is measured
                using distance or similarity metrics.
            </p>

            <ul>
                <li><strong>Cosine Similarity (Recommended):</strong> Measures similarity based on vector direction and works well for embeddings.</li>
                <li><strong>Euclidean Distance:</strong> Measures straight-line distance but may be sensitive to vector magnitude.</li>
            </ul>

            <div class="alert alert-success">
                In practice, <strong>Cosine Similarity + Nearest Neighbor</strong> is the most common approach when working with SBERT embeddings.
            </div>

            <!-- <h4>Nearest Neighbor with Embeddings</h4>
            <p>
                Once documents are represented as vectors, nearest neighbor search identifies
                which documents are closest in meaning.
            </p>

            <pre><code class="language-python">
# Conceptual example
query_embedding → compare with all document embeddings
retrieve top-k most similar documents
</code></pre>

            <p>
                This enables semantic search, recommendation systems, clustering,
                and intelligent document retrieval.
            </p>

            <h4>Real-World Applications</h4>
            <ul>
                <li>Semantic search engines</li>
                <li>FAQ and chatbot matching</li>
                <li>Document clustering and grouping</li>
                <li>Duplicate or similar content detection</li>
                <li>Multilingual feedback analysis</li>
            </ul>

            <div class="alert alert-warning">
                Very long documents may need to be split into smaller chunks before embedding,
                since SBERT models have input length limits.
            </div> -->
        </section>


        <!-- Nearest Neighbor -->
        <section class="section">
            <h2>Nearest Neighbor in Text Mining</h2>

            <p>
                Once words or documents are converted into vectors (using TF-IDF or embeddings),
                similarity between them can be measured using distance metrics such as:
            </p>
            <!-- 
            <ul>
                <li><strong>Euclidean Distance</strong></li>
                <p>
                    Euclidean Distance measures the straight-line distance between two vectors
                    in a multi-dimensional space. It is based on the Pythagorean theorem.
                </p>
                <li><strong>Cosine Similarity</strong></li>
                <p>
                    Cosine Similarity measures the angle between two vectors rather than
                    the distance between their positions. It evaluates how aligned two vectors are.
                </p>
            </ul> -->

            <p>
                The <strong>Nearest Neighbor</strong> method identifies the closest vectors
                to a given word or document in the vector space.
            </p>

            <div class="alert alert-success">
                Nearest Neighbor helps answer:
                <strong>"Which words or documents are most similar to this one?"</strong>
            </div>

            <h5>Example: Finding Similar Words</h5>

            <pre><code class="language-python">
# Conceptual example
similar_words("king") → ["queen", "monarch", "prince"]
</code></pre>

            <p>
                If the cosine similarity between vectors is high (close to 1),
                the words are considered semantically similar.
            </p>

            <h5>Business Applications</h5>
            <ul>
                <li>Product recommendation systems</li>
                <li>Document similarity detection</li>
                <li>Chatbots and question-answer systems</li>
                <li>Plagiarism detection</li>
            </ul>

            <h5>Nearest Neighbor in Classification</h5>
            <p>
                In predictive modeling, the <strong>K-Nearest Neighbor (KNN)</strong> algorithm
                classifies a new document based on the labels of its closest neighbors.
            </p>

            <pre><code class="language-python">
# Simplified KNN idea
Find k closest documents
Assign majority label among neighbors
</code></pre>

            <div class="alert alert-info">
                Word embeddings + Nearest Neighbor form the foundation of many modern AI systems,
                including recommendation engines and semantic search.
            </div>
        </section>


        <!-- Applications -->
        <section class="section">
            <h2>Applications of Text Mining</h2>
            <p>
                Text mining supports many real-world business and organizational applications:
            </p>

            <ul>
                <li>Sentiment Analysis (positive, negative, neutral)</li>
                <li>Spam Email Detection</li>
                <li>Topic Modeling</li>
                <li>Customer Feedback Evaluation</li>
                <li>Fraud Detection</li>
            </ul>
        </section>

        <!-- Predictive Connection -->
        <section class="section">
            <h2>Text Mining in Predictive Analytics</h2>
            <p>
                Text mining becomes powerful when integrated with predictive modeling.
                For example, customer review sentiment can be used to predict churn,
                or complaint frequency can forecast service demand.
            </p>

            <div class="alert alert-success">
                Text mining transforms qualitative feedback into quantitative predictors.
            </div>
        </section>

        <!-- Challenges -->
        <section class="section">
            <h2>Challenges</h2>
            <p>
                Despite its advantages, text mining presents several challenges:
            </p>

            <ul>
                <li>Ambiguity and sarcasm in language</li>
                <li>Multiple languages and slang</li>
                <li>Data privacy concerns</li>
                <li>Large-scale data processing requirements</li>
            </ul>
        </section>

        <!-- Ethics -->
        <section class="section">
            <h2>Ethical Considerations</h2>
            <p>
                Organizations must ensure compliance with data privacy regulations when collecting
                and analyzing textual data. Ethical analytics requires transparency,
                responsible use of AI, and protection of sensitive information.
            </p>
        </section>

        <!-- Reflection -->
        <section class="section">
            <h2>Reflection Questions</h2>
            <ol>
                <li>Why is text preprocessing essential before modeling?</li>
                <li>How does TF-IDF differ from simple word counting?</li>
                <li>Give one example of how text mining can support predictive analytics.</li>
                <li>What ethical risks exist when analyzing social media data?</li>
            </ol>
        </section>

    </div>

    <?php $this->load->view('web_to_image') ?>

</body>

</html>