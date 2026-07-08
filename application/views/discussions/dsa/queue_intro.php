<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Queues in C (Array Implementation)</title>

    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header>
        <h1>Introduction to Queues (C Programming)</h1>
        <p>Learn how the queue data structure works using arrays and the FIFO principle.</p>
    </header>

    <div class="content">

        <h2>Objectives</h2>

        <ul>
            <li>Understand the FIFO principle.</li>
            <li>Identify and trace the changes in front and rear positions.</li>
            <li>Write a working C program implementing queues using arrays.</li>
        </ul>

        <h2>What is a Queue?</h2>
        <p>A <strong>queue</strong> is a linear data structure that follows the <b>FIFO</b> rule — First In, First Out. This means the first item inserted will be the first one removed.</p>

        <p>Real-world examples:</p>
        <ul>
            <li>Waiting line in a grocery store</li>
            <li>Print queue in a computer</li>
            <li>Customer service ticketing system</li>
        </ul>

        <h2>Queue vs Stack</h2>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Feature</th>
                    <th>Queue</th>
                    <th>Stack</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Principle</td>
                    <td>FIFO (First In First Out)</td>
                    <td>LIFO (Last In First Out)</td>
                </tr>
                <tr>
                    <td>Main Operations</td>
                    <td>enqueue (insert), dequeue (remove)</td>
                    <td>push (insert), pop (remove)</td>
                </tr>
                <tr>
                    <td>Real-life Example</td>
                    <td>People falling in line</td>
                    <td>Stack of plates</td>
                </tr>
            </tbody>
        </table>

        <h2>Operations of a Queue</h2>
        <ul>
            <li><b>enqueue(x)</b> – Insert an element at the rear</li>
            <li><b>dequeue()</b> – Remove the front element</li>
            <li><b>peek()</b> – View the front element</li>
            <li><b>isEmpty()</b> – Check if the queue is empty</li>
            <li><b>isFull()</b> – Check if the queue is full (for fixed arrays)</li>
        </ul>

        <h2>Queue Implementation in C (Using Array)</h2>

        <p>Reasons to implement queues using arrays:</p>
        <ul>
            <li><b>Memory Efficient: </b> Array elements do not hold the next elements address like linked list nodes do.</li>
            <li><b>Easier to implement and understand: </b> Using arrays to implement queues require less code than using linked lists, and for this reason it is typically easier to understand as well.</li>
        </ul>

        <p>Reasons for not using arrays to implement queues:</p>

        <ul>
            <li><b>Fixed size: </b> An array occupies a fixed part of the memory. This means that it could take up more memory than needed, or if the array fills up, it cannot hold more elements. And resizing an array can be costly.</li>
            <li><b>Shifting cost: </b> Dequeue causes the first element in a queue to be removed, and the other elements must be shifted to take the removed elements' place. This is inefficient and can cause problems, especially if the queue is long.</li>
        </ul>
<!-- 
        <p>This is a simple static implementation of a queue with fixed size.</p>

        <pre><code class="language-c">#include &lt;stdio.h&gt;
#define MAX 5

int queue[MAX];
int front = 0;
int rear = 0;

void enqueue(int x) {
    if (rear == MAX) {
        printf("Queue is full!\n");
        return;
    }
    queue[rear++] = x;
    printf("Enqueued %d\n", x);
}

int dequeue() {
    if (front == rear) {
        printf("Queue is empty!\n");
        return -1;
    }
    int val = queue[front++];
    printf("Dequeued %d\n", val);
    return val;
}

int peek() {
    if (front == rear) {
        printf("Queue is empty!\n");
        return -1;
    }
    return queue[front];
}

int main() {
    enqueue(10);
    enqueue(20);
    enqueue(30);

    printf("Front element: %d\n", peek());

    dequeue();
    printf("Front element: %d\n", peek());

    return 0;
}
</code></pre> -->

This is a simple static implementation of a queue with fixed size and initialized with -1.

<pre><code class="language-c">#include &lt;stdio.h&gt;
#define SIZE 5

int queue[SIZE];
int front = -1;
int rear = -1;

int isEmpty() {
    return (front == -1 || front > rear);
}

int isFull() {
    return (rear == SIZE - 1);
}

void enqueue(int value) {
    if (isFull()) {
        printf("Queue is FULL!\n");
    } else {
        if (front == -1) front = 0;
        rear++;
        queue[rear] = value;
        printf("%d inserted.\n", value);
    }
}

void dequeue() {
    if (isEmpty()) {
        printf("Queue is EMPTY!\n");
    } else {
        printf("%d removed.\n", queue[front]);
        front++;
    }
}

void display() {
    if (isEmpty()) {
        printf("Queue is EMPTY!\n");
    } else {
        printf("Queue: ");
        for (int i = front; i <= rear; i++) {
            printf("%d ", queue[i]);
        }
        printf("\n");
    }
}


int main(){
	enqueue(25);
    enqueue(27);
    dequeue();
}
</code></pre>

This is a simple static implementation of a queue array where the remaining elements move one position to the left.

<pre><code class="language-c">#include &lt;stdio.h&gt;
#define SIZE 5

int queue[SIZE];
int rear = -1;

int isEmpty() {
    return (rear == -1);
}

int isFull() {
    return (rear == SIZE - 1);
}

void enqueue(int value) {
    if (isFull()) {
        printf("Queue is FULL!\n");
    } else {
        rear++;
        queue[rear] = value;
        printf("%d inserted.\n", value);
    }
}

void dequeue() {
    if (isEmpty()) {
        printf("Queue is EMPTY!\n");
    } else {
        printf("%d removed.\n", queue[0]);

        for (int i = 0; i < rear; i++) {
            queue[i] = queue[i + 1];
        }

        rear--;
    }
}

void display() {
    if (isEmpty()) {
        printf("Queue is EMPTY!\n");
    } else {
        printf("Queue: ");
        for (int i = 0; i <= rear; i++) {
            printf("%d ", queue[i]);
        }
        printf("\n");
    }
}

int main() {
    enqueue(10);
    enqueue(20);
    enqueue(30);
    display();

    dequeue();
    display();

    enqueue(40);
    enqueue(50);
    enqueue(60);
    enqueue(80);
    enqueue(90);
    display();

    return 0;
}

</code></pre>

        <h2>Key Notes</h2>
        <ul>
            <li>Front always points to the first valid element.</li>
            <li>Rear points to the next available slot.</li>
            <li>The simple array version does <b>not</b> reuse deleted spaces.</li>
        </ul>

        <!-- <pre><code class="language-c">rear = (rear + 1) % MAX;
front = (front + 1) % MAX;</code></pre> -->

        <p>This avoids wasted space.</p>

        <h2>Hands-On Activity</h2>
        <ul>
            <li>Create a program that allows multiple enqueues and dequeues using user input.</li>
            <li>Modify the program to detect when the queue is full or empty.</li>
            <li>Add a function that prints the full content of the queue.</li>
        </ul>

        <h2>Summary</h2>
        <ul>
            <li>Queues use FIFO.</li>
            <li>Common operations: enqueue, dequeue, peek.</li>
            <li>Static arrays limit size.</li>
            <li>Circular queues reuse empty spaces.</li>
        </ul>

        <div class="references">
            <h2>References</h2>
            <ul>
                <li><a href="https://www.w3schools.com/dsa/dsa_data_queues.php" target="_blank">W3Schools: Data Structures - Queues</a></li>
                <li><a href="https://www.programiz.com/dsa/queue" target="_blank">Programiz: Queue Data Structure</a></li>
            </ul>
        </div>

    </div>

</body>

</html>