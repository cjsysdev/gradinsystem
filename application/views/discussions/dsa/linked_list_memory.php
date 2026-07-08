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
                    <h1 class="mb-3" style="font-size:2.2rem;">Linked Lists in Memory</h1>
                    <hr>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Computer Memory</h3>
<p>To explain what linked lists are, and how linked lists are different from arrays, we need to understand some basics about how computer memory works.</p>
<p>Computer memory is the storage your program uses when it is running. This is where your variables, arrays and linked lists are stored.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Variables in Memory</h3>
<p>Let's imagine that we want to store the integer "17" in a variable myNumber. For simplicity, let's assume the integer is stored as two bytes (16 bits), and the address in memory to myNumber is 0x7F25.</p>
<p>0x7F25 is actually the address to the first of the two bytes of memory where the myNumber integer value is stored. When the computer goes to 0x7F25 to read an integer value, it knows that it must read both the first and the second byte, since integers are two bytes on this specific computer.</p>
<p>The image below shows how the variable myNumber = 17 is stored in memory.</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_memory_new.png') ?>" alt="A variable stored in memory" class="img-fluid"></div>
<p>The example above shows how an integer value is stored on the simple, but popular, Arduino Uno microcontroller. This microcontroller has an 8 bit architecture with 16 bit address bus and uses two bytes for integers and two bytes for memory addresses. For comparison, personal computers and smart phones use 32 or 64 bits for integers and addresses, but the memory works basically in the same way.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Arrays in Memory</h3>
<p>To understand linked lists, it is useful to first know how arrays are stored in memory.</p>
<p>Elements in an array are stored contiguously in memory. That means that each element is stored right after the previous element.</p>
<p>The image below shows how an array of integers myArray = [3,5,13,2] is stored in memory. We use a simple kind of memory here with two bytes for each integer, like in the previous example, just to get the idea.</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_arraymemory_new.png') ?>" alt="An array stored in memory" class="img-fluid"></div>
<p>The computer has only got the address of the first byte of myArray, so to access the 3rd element with code myArray[2] the computer starts at 0x7F23 and jumps over the two first integers. The computer knows that an integer is stored in two bytes, so it jumps 2x2 bytes forward from 0x7F23 and reads value 13 starting at address 0x7F27.</p>
<p>When removing or inserting elements in an array, every element that comes after must be either shifted up to make place for the new element, or shifted down to take the removed element's place. Such shifting operations are time consuming and can cause problems in real-time systems for example.</p>
<p>The image below shows how elements are shifted when an array element is removed.</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_array_removed_shifting.png') ?>" alt="Removing an element from an array" class="img-fluid"></div>
<p>Manipulating arrays is also something you must think about if you are programming in C, where you have to explicitly move other elements when inserting or removing an element. In C this does not happen in the background.</p>
<p>In C you also need to make sure that you have allocated enough space for the array to start with, so that you can add more elements later.</p>
<p>You can read more about arrays on this previous DSA tutorial page.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Linked Lists in Memory</h3>
<p>Instead of storing a collection of data as an array, we can create a linked list.</p>
<p>Linked lists are used in many scenarios, like dynamic data storage, stack and queue implementation or graph representation, to mention some of them.</p>
<p>A linked list consists of nodes with some sort of data, and at least one pointer, or link, to other nodes.</p>
<p>A big benefit with using linked lists is that nodes are stored wherever there is free space in memory, the nodes do not have to be stored contiguously right after each other like elements are stored in arrays. Another nice thing with linked lists is that when adding or removing nodes, the rest of the nodes in the list do not have to be shifted.</p>
<p>The image below shows how a linked list can be stored in memory. The linked list has four nodes with values 3, 5, 13 and 2, and each node has a pointer to the next node in the list.</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_memory2_new.png') ?>" alt="Linked list nodes in memory" class="img-fluid"></div>
<p>Each node takes up four bytes. Two bytes are used to store an integer value, and two bytes are used to store the address to the next node in the list. As mentioned before, how many bytes that are needed to store integers and addresses depend on the architecture of the computer. This example, like the previous array example, fits with a simple 8-bit microcontroller architecture.</p>
<p>To make it easier to see how the nodes relate to each other, we will display nodes in a linked list in a simpler way, less related to their memory location, like in the image below:</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_singlenode.svg') ?>" alt="Linked list single node" class="img-fluid"></div>
<p>If we put the same four nodes from the previous example together using this new visualization, it looks like this:</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_exwithvalues.svg') ?>" alt="Linked list example with addresses and values." class="img-fluid"></div>
<p>As you can see, the first node in a linked list is called the "Head", and the last node is called the "Tail".</p>
<p>Unlike with arrays, the nodes in a linked list are not placed right after each other in memory. This means that when inserting or removing a node, shifting of other nodes is not necessary, so that is a good thing.</p>
<p>Something not so good with linked lists is that we cannot access a node directly like we can with an array by just writing myArray[5] for example. To get to node number 5 in a linked list, we must start with the first node called "head", use that node's pointer to get to the next node, and do so while keeping track of the number of nodes we have visited until we reach node number 5.</p>
<p>Learning about linked lists helps us to better understand concepts like memory allocation and pointers.</p>
<p>Linked lists are also important to understand before learning about more complex data structures such as trees and graphs, that can be implemented using linked lists.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Memory in Modern Computers</h3>
<p>So far on this page we have used the memory in an 8 bit microcontroller as an example to keep it simple and easier to understand.</p>
<p>Memory in modern computers work in the same way in principle as memory in an 8 bit microcontroller, but more memory is used to store integers, and more memory is used to store memory addresses.</p>
<p>The code below gives us the size of an integer and the size of a memory address on the server we are running these examples on.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<!-- Try it Yourself example -->
<p>Code written in C:</p>
<p>The code example above only runs in C because Java and Python runs on an abstraction level above specific/direct memory allocation.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Linked List Implementation in C</h3>
<p>Let's implement this linked list from earlier:</p>
<div class="text-center my-3"><img src="<?= base_url('assets/img_linkedlists_exwithvalues.svg') ?>" alt="Linked list example with addresses and values." class="img-fluid"></div>
<p>Let's implement this linked list in C to see a concrete example of how linked lists are stored in memory.</p>
<p>In the code below, after including the libraries, we create a node struct which is like a class that represents what a node is: the node contains data and a pointer to the next node.</p>
<p>The createNode() function allocates memory for a new node, fills in the data part of the node with an integer given as an argument to the function, and returns the pointer (memory address) to the new node.</p>
<p>The printList() function is just for going through the linked list and printing each node's value.</p>
<p>Inside the main() function, four nodes are created, linked together, printed, and then the memory is freed. It is good practice to free memory after we are done using it to avoid memory leaks. Memory leak is when memory is not freed after use, gradually taking up more and more memory.</p>
<h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
<!-- Try it Yourself example -->
<p>A basic linked list in C:</p>
<p>To print the linked list in the code above, the printList() function goes from one node to the next using the "next" pointers, and that is called "traversing" or "traversal" of the linked list. You will learn more about linked list traversal and other linked list operations on the Linked Lists Oprations page.</p>


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
