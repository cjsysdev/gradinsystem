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
                    <h1 class="mb-3" style="font-size:2.2rem;">Allocate Memory</h1>
                    <hr>
                    <p>The process of reserving memory is called allocation. The way to allocate memory depends on the type of memory.</p>
<p>C has two types of memory: Static memory and dynamic memory.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Static Memory</h3>
<p>Static memory is memory that is reserved for variables before the program runs. Allocation of static memory is also known as compile time memory allocation.</p>
<p>C automatically allocates memory for every variable when the program is compiled.</p>
<p>For example, if you create an integer array of 20 students (e.g. for a summer semester), C will reserve space for 20 elements which is typically 80 bytes of memory (20 * 4):</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-c">
<?= htmlspecialchars('int students[20];
printf("%zu", sizeof(students)); // 80 bytes')?>
</code></pre>
<!-- Try it Yourself example -->
<pre><code class="language-c">
<?= htmlspecialchars('int students[20];
printf("%zu", sizeof(students)); // 80 bytes')?>
</code></pre>
<p>But when the semester starts, it turns out that only 12 students are attending. Then you have wasted the space of 8 unused elements.</p>
<p>Since you are not able to change the size of the array, you are left with unnecessary reserved memory.</p>
<p>Note that the program will still run, and it is not damaged in any way. But if your program contains a lot of this kind of code, it may run slower than it optimally could.</p>
<p>If you want better control of allocated memory, take a look at Dynamic Memory below.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Dynamic Memory</h3>
<p>Dynamic memory is memory that is allocated after the program starts running. Allocation of dynamic memory can also be referred to as runtime memory allocation.</p>
<p>Unlike with static memory, you have full control over how much memory is being used at any time. You can write code to determine how much memory you need and allocate it.</p>
<p>Dynamic memory does not belong to a variable, it can only be accessed with pointers.</p>
<p>To allocate dynamic memory, you can use the malloc() or calloc() functions. It is necessary to include the <stdlib.h> header to use them. The malloc() and calloc() functions allocate some memory and return a pointer to its address.</p>
<pre><code class="language-c">
<?= htmlspecialchars('int *ptr1 = malloc(
size
);
int *ptr2 = calloc(
amount
,
size
);')?>
</code></pre>
<pre><code class="language-c">
<?= htmlspecialchars('int *ptr1 = malloc(
size
);
int *ptr2 = calloc(
amount
,
size
);')?>
</code></pre>
<p>The malloc() function has one parameter, size, which specifies how much memory to allocate, measured in bytes.</p>
<p>The calloc() function has two parameters:</p>
<ul>
<li>amount - Specifies the amount of items to allocate</li>
<li>size - Specifies the size of each item measured in bytes</li>
</ul>
<p>Note: The data in the memory allocated by malloc() is unpredictable. To avoid unexpected values, make sure to write something into the memory before reading it.</p>
<p>Unlike malloc(), the calloc() function writes zeroes into all of the allocated memory. However, this makes calloc() slightly less efficient.</p>
<p>The best way to allocate the right amount of memory for a data type is to use the sizeof operator:</p>
<pre><code class="language-c">
<?= htmlspecialchars('int *ptr1, *ptr2;
ptr1 = malloc(sizeof(*ptr1));
ptr2 = calloc(1, sizeof(*ptr2));')?>
</code></pre>
<pre><code class="language-c">
<?= htmlspecialchars('int *ptr1, *ptr2;
ptr1 = malloc(sizeof(*ptr1));
ptr2 = calloc(1, sizeof(*ptr2));')?>
</code></pre>
<p>Be careful: sizeof(*ptr1) tells C to measure the size of the data at the address. If you forget the * and write sizeof(ptr1) instead, it will measure the size of the pointer itself, which is the (usually) 8 bytes that are needed to store a memory address.</p>
<p>Note: The sizeof operator cannot measure how much dynamic memory is allocated. When measuring dynamic memory, it only tells you the size of the data type of the memory. For example, if you reserve space for 5 float values, the sizeof operator will return 4, which is the number of bytes needed for a single float value.</p>
<p>Let's use dynamic memory to improve the students example above.</p>
<p>As noted previously, we cannot use sizeof to 
measure how much memory was allocated, we have to calculate that by multiplying 
the amount of items by the size of the data type:</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<pre><code class="language-c">
<?= htmlspecialchars('int *students;
int numStudents = 12;
students = calloc(numStudents,
 sizeof(*students));
printf("%d", numStudents * sizeof(*students)); // 48
 bytes')?>
</code></pre>
<!-- Try it Yourself example -->
<pre><code class="language-c">
<?= htmlspecialchars('int *students;
int numStudents = 12;
students = calloc(numStudents,
 sizeof(*students));
printf("%d", numStudents * sizeof(*students)); // 48
 bytes')?>
</code></pre>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Notes</h3>
<p>When working with dynamic memory allocation, you should also check for errors and 
free memory at the end of the program. You will learn more about this in the next chapters.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Stack Memory</h3>
<p>For completeness, it is worth mentioning stack memory. Stack memory is a type of dynamic memory which is reserved for variables that are declared inside functions. Variables declared inside a function use stack memory rather than static memory.</p>
<p>When a function is called, stack memory is allocated for the variables in the function. When the function returns the stack memory is freed.</p>
<p>It is good to be aware of stack memory to be able to handle the memory usage of nested function calls and recursion. Recursion that repeats itself too many times may take up too much stack memory. When that happens it is called a stack overflow.</p>

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
