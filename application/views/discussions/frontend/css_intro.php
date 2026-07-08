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
                    <h1 class="mb-3" style="font-size:2.2rem;">Introduction</h1>
                    <hr>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">What is CSS?</h3>
<p>CSS is the language we use to style a Web page.</p>
<ul>
<li>CSS stands for Cascading Style Sheets</li>
<li>CSS describes how HTML elements are to be displayed on screen, 
 paper, or in other media</li>
<li>CSS saves a lot of work. It can control the layout of 
 multiple web pages all at once</li>
<li>External stylesheets are stored in CSS files</li>
</ul>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Why Use CSS?</h3>
<p>CSS is used to define styles for your web pages, including the design, layout 
and variations in display for different devices and screen sizes.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">CSS Example</h3>
<pre><code class="language-css">
<?= htmlspecialchars('
body
{
background-color: lightblue;
}
h1
{
color: white;
text-align: center;
}
p
{
font-family: verdana;
font-size: 20px;
}') ?>
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">CSS Saves a Lot of Work!</h3>
<p>The CSS definitions are normally saved in an external .css file.</p>
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
