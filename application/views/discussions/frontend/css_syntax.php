<?php $this->load->view('header'); ?>

<!-- Highlight.js CSS -->
<link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
<!-- Highlight.js JS -->
<script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="mb-3" style="font-size:2.2rem;">Syntax</h1>
                    <hr>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">CSS Syntax</h3>
<p>A CSS rule consists of a selector and a declaration block:</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_selector.gif') ?>" alt="CSS selector" class="img-fluid"></div>
<p>The selector points to the HTML element you want to style.</p>
<p>The declaration block contains one or more declarations separated by 
semicolons.</p>
<p>Each declaration includes a CSS property name and a value, separated by a colon.</p>
<p>Multiple CSS declarations are separated with semicolons, and declaration 
blocks are surrounded by curly braces.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<!-- Try it Yourself example -->
<p>In this example all <?=htmlspecialchars('<p>') ?> elements will be center-aligned, with a red 
text color:</p>
<pre><code class="language-css">
    <?= htmlspecialchars('
p
{
color: red;
text-align: center;
}
')?>
</code></pre>
<ul>
<li>p is a selector in CSS (it points to the HTML element you want to style: <?= htmlspecialchars('<p>') ?>).</li>
<li>color is a property, and red is the property value</li>
<li>text-align is a property, and center is the property value</li>
</ul>
<p>You will learn much more about CSS selectors and CSS properties in the next chapters!</p>
                    <div class="mt-4">
                        <a href="<?= base_url('discussion') ?>" class="btn btn-outline-primary">Back to Topics</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Highlight.js JS -->
<script>
    hljs.highlightAll();
</script>

<?php $this->load->view('footer'); ?>
