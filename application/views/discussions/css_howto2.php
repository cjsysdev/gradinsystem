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
                    <h1 class="mb-3" style="font-size:2.2rem;">How To Add CSS</h1>
                    <hr>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">How to Add CSS</h3>
<p>When a browser reads a style sheet, it will format the HTML document according to the information in the style sheet.</p>
<p>There are three ways of inserting a style sheet:</p>
<ul>
<li>External CSS</li>
<li>Internal CSS</li>
<li>Inline CSS</li>
</ul>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">External CSS</h3>
<p>With an
external style sheet, you can change the look of an entire website by changing 
just one file!</p>
<p>Each HTML page must include a reference to the external style sheet file inside 
the <link> element, inside the head section.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-html">
<?= htmlspecialchars('<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="mystyle.css">
</head>
<body>
<h1>This is a heading</h1>
<p>This is a paragraph.</p>
</body>
</html>')?>
</code></pre>
<!-- Try it Yourself example -->
<p>External styles are defined within the <link> element, inside the <head> section of an HTML page:</p>
<pre><code class="language-html">
<?= htmlspecialchars('<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="mystyle.css">
</head>
<body>
<h1>This is a heading</h1>
<p>This is a paragraph.</p>
</body>
</html>')?>
</code></pre>
<p>An external style sheet can be written in any text editor, and must be saved with a .css extension.</p>
<p>The external .css file should not contain any HTML tags.</p>
<p>Here is how the "mystyle.css" file looks:</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">"mystyle.css"</h3>
<pre><code class="language-html">
<?= htmlspecialchars('body {
background-color: lightblue;
}
h1 {
color: navy;
margin-left: 20px;
}')?>
</code></pre>
<pre><code class="language-html">
<?= htmlspecialchars('body {
background-color: lightblue;
}
h1 {
color: navy;
margin-left: 20px;
}')?>
</code></pre>
<p>Note: Do not add a space between the property value (20) and the unit 
 (px):
 Incorrect (space): margin-left: 20 px;
 Correct (no space): margin-left: 20px;</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Internal CSS</h3>
<p>An internal style sheet may be used if one single HTML page has a unique style.</p>
<p>The internal style is defined inside the <style> element, inside the head 
section.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-html">
<?= htmlspecialchars('<!DOCTYPE html>
<html>
<head>
<style>
body {
background-color: linen;
}
h1 {
color: maroon;
margin-left: 40px;
}
</style>
</head>
<body>
<h1>This is a
 heading</h1>
<p>This is a paragraph.</p>
</body>
</html>')?>
</code></pre>
<!-- Try it Yourself example -->
<p>Internal styles are defined within the <style> element, inside the <head> section of an HTML page:</p>
<pre><code class="language-html">
<?= htmlspecialchars('<!DOCTYPE html>
<html>
<head>
<style>
body {
background-color: linen;
}
h1 {
color: maroon;
margin-left: 40px;
}
</style>
</head>
<body>
<h1>This is a
 heading</h1>
<p>This is a paragraph.</p>
</body>
</html>')?>
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Inline CSS</h3>
<p>An inline style may be used to apply a unique style for a single element.</p>
<p>To use inline styles, add the style attribute to the relevant element. The
style attribute can contain any CSS property.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-html">
<?= htmlspecialchars('<!DOCTYPE html>
<html>
<body>
<h1 style="color:blue;text-align:center;">This
 is a heading</h1>
<p style="color:red;">This is a paragraph.</p>
</body>
</html>')?>
</code></pre>
<!-- Try it Yourself example -->
<p>Inline styles are defined within the "style" attribute of the relevant 
 element:</p>
<pre><code class="language-html">
<?= htmlspecialchars('<!DOCTYPE html>
<html>
<body>
<h1 style="color:blue;text-align:center;">This
 is a heading</h1>
<p style="color:red;">This is a paragraph.</p>
</body>
</html>')?>
</code></pre>
<p>Tip: An inline style loses many of the advantages of a style sheet (by mixing
content with presentation). Use this method sparingly.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Multiple Style Sheets</h3>
<p>If some properties have been defined for the same selector (element) in different style sheets,
the value from the last read style sheet will be used.</p>
<pre><code class="language-html">
<?= htmlspecialchars('h1
{
color: navy;
}')?>
</code></pre>
<p>Assume that an external style sheet has the following style for the <h1> element:</p>
<pre><code class="language-html">
<?= htmlspecialchars('h1
{
color: navy;
}')?>
</code></pre>
<pre><code class="language-html">
<?= htmlspecialchars('h1
{
color: orange;
}')?>
</code></pre>
<p>Then, assume that an internal style sheet also has the following style for the <h1> element:</p>
<pre><code class="language-html">
<?= htmlspecialchars('h1
{
color: orange;
}')?>
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-html">
<?= htmlspecialchars('<head>
<link rel="stylesheet" type="text/css" href="mystyle.css">
<style>
h1 {
color: orange;
}
</style>
</head>')?>
</code></pre>
<!-- Try it Yourself example -->
<p>If the internal style is defined after the link to the external style sheet, the <h1> elements will be 
"orange":</p>
<pre><code class="language-html">
<?= htmlspecialchars('<head>
<link rel="stylesheet" type="text/css" href="mystyle.css">
<style>
h1 {
color: orange;
}
</style>
</head>')?>
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-html">
<?= htmlspecialchars('<head>
<style>
h1 {
color: orange;
}
</style>
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>')?>
</code></pre>
<!-- Try it Yourself example -->
<p>However, if the internal style is defined before the link to the external style sheet, the <h1> elements will be 
"navy":</p>
<pre><code class="language-html">
<?= htmlspecialchars('<head>
<style>
h1 {
color: orange;
}
</style>
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>')?>
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Cascading Order</h3>
<p>What style will be used when there is more than one style specified for an HTML element?</p>
<p>All the styles in a page will "cascade" into a new "virtual" style
sheet by the following rules, where number one has the highest priority:</p>
<ol>
<li>Inline style (inside an HTML element)</li>
<li>External and internal style sheets (in the head section)</li>
<li>Browser default</li>
</ol>
<p>So, an inline style has the highest priority, and will override external and 
internal styles and browser defaults.</p>
<p>Try it Yourself »</p>
<p>Ever heard about W3Schools Spaces? Here you can create your own website, or save code snippets for later use, for free.</p>
<p>* no credit card required</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Video: How to add CSS to HTML</h3>
<div class="text-center my-3"><img src="https://www.w3schools.com/css/images/yt_logo_rgb_dark.png" alt="Tutorial on YouTube" class="img-fluid"></div>
<div class="text-center my-3"><img src="https://www.w3schools.com/css/images/css_howto.png" alt="Tutorial on YouTube" class="img-fluid"></div>

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
