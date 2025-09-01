<?php $this->load->view('header'); ?>

<!-- Highlight.js CSS -->
<link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
<!-- Highlight.js JS -->
<script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>

<div class="content container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="mb-3" style="font-size:2.2rem;">Structures (structs)</h1>
                    <hr>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Structures</h3>
                    <p>Structures (also called structs) are a way to group several related variables into one place.</p>
                    <p>Each variable in the structure is
                        known as a member of the structure.</p>
                    <p>Unlike an array, a structure can contain many
                        different data types (int, float, char, etc.).</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Create a Structure</h3>
                    <p>You can create a structure by using the struct
                        keyword and declare each of its members inside curly braces:</p>
                    <pre><code class="language-c">
struct MyStructure {   // Structure declaration
  int myNum;           // Member (int variable)
  char myLetter;       // Member (char variable)
}; // End the structure with a semicolon
 // End the structure with a semicolon
</code></pre>
                    <p>To access the structure, you must create a variable of it.</p>
                    <p>Use the struct keyword
                        inside the main() method, followed by the name
                        of the structure and then the name of the structure variable:</p>
                    <pre><code class="language-c">
struct myStructure {
  int myNum;
  char myLetter;
};

int main() {
  struct myStructure s1;
  return 0;
}
</code></pre>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Access Structure Members</h3>
                    <p>To access members of a structure, use the dot syntax (.):</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
// Create a structure called myStructure
struct myStructure {
  int myNum;
  char myLetter;
};

int main() {
  // Create a structure variable of myStructure called s1
  struct myStructure s1;

  // Assign values to members of s1
  s1.myNum = 13;
  s1.myLetter = 'B';

  // Print values
  printf("My number: %d\n", s1.myNum);
  printf("My letter: %c\n", s1.myLetter);

  return 0;
}
</code></pre>
                    <!-- Try it Yourself example -->
                    <p>Now you can easily create multiple structure variables with different values, using just one structure:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
// Create different struct variables
struct myStructure s1;
struct myStructure s2;

// Assign values to different struct variables
s1.myNum = 13;
s1.myLetter = 'B';

s2.myNum = 20;
s2.myLetter = 'C';
</code></pre>
                    <!-- Try it Yourself example -->
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">What About Strings in Structures?</h3>
                    <p>Remember that strings in C are actually an array of characters, and
                        unfortunately, you can't assign a value to an array like this:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
struct myStructure {
  int myNum;
  char myLetter;
  char myString[30];  // String
};

int main() {
  struct myStructure s1;

  // Trying to assign a value to the string
  s1.myString = "Some text";

  // Trying to print the value
  printf("My string: %s", s1.myString);

  return 0;
}
</code></pre>
                    <!-- Try it Yourself example -->
                    <p>However, there is a solution for this! You can use the strcpy()
                        function and assign the value to s1.myString, like this:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
struct myStructure {
  int myNum;
  char myLetter;
  char myString[30]; // String
};

int main() {
  struct myStructure s1;

  // Assign a value to the string using the strcpy function
  strcpy(s1.myString, "Some text");

  // Print the value
  printf("My string: %s", s1.myString);

  return 0;
}
</code></pre>
                    <!-- Try it Yourself example -->
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Simpler Syntax</h3>
                    <p>You can also assign values to members of a structure variable at declaration time,
                        in a single line.</p>
                    <p>Just insert the values in a comma-separated list
                        inside curly braces {}. Note that you don't
                        have to use the strcpy() function for string
                        values with this
                        technique:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
// Create a structure
struct myStructure {
  int myNum;
  char myLetter;
  char myString[30];
};

int main() {
  // Create a structure variable and assign values to it
  struct myStructure s1 = {13, 'B', "Some text"};

  // Print values
  printf("%d %c %s", s1.myNum, s1.myLetter, s1.myString);

  return 0;
}
</code></pre>
                    <!-- Try it Yourself example -->
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Copy Structures</h3>
                    <p>You can also assign one structure to another.</p>
                    <p>In the following example, the values of s1 are copied to s2:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
struct myStructure s1 = {13, 'B', "Some text"};
struct myStructure s2;

s2 = s1;
</code></pre>
                    <!-- Try it Yourself example -->
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Modify Values</h3>
                    <p>If you want to change/modify a value, you can use the dot syntax (.).</p>
                    <p>And to modify a
                        string value, the strcpy() function is useful again:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
struct myStructure {
  int myNum;
  char myLetter;
  char myString[30];
};

int main() {
  // Create a structure variable and assign values to it
  struct myStructure s1 = {13, 'B', "Some text"};

  // Modify values
  s1.myNum = 30;
  s1.myLetter = 'C';
  strcpy(s1.myString, "Something else");

  // Print values
  printf("%d %c %s", s1.myNum, s1.myLetter, s1.myString);

  return 0;
}
</code></pre>
                    <!-- Try it Yourself example -->
                    <p>Modifying values are especially useful when you copy structure values:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
// Create a structure variable and assign values to it
struct myStructure s1 = {13, 'B', "Some text"};

// Create another structure variable
struct myStructure s2;

// Copy s1 values to s2
s2 = s1;

// Change s2 values
s2.myNum = 30;
s2.myLetter = 'C';
strcpy(s2.myString, "Something else");

// Print values
printf("%d %c %s\n", s1.myNum, s1.myLetter, s1.myString);
printf("%d %c %s\n", s2.myNum, s2.myLetter, s2.myString);
</code></pre>
                    <!-- Try it Yourself example -->
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Real-Life Example</h3>
                    <p>Use a structure to store different information about Cars:</p>
                    <h3 class="mt-4 mb-2" style="font-size:1.3rem;">Example</h3>
                    <pre><code class="language-c">
struct Car {
  char brand[50];
  char model[50];
  int year;
};

int main() {
  struct Car car1 = {"BMW", "X5", 1999};
  struct Car car2 = {"Ford", "Mustang", 1969};
  struct Car car3 = {"Toyota", "Corolla", 2011};

  printf("%s %s %d\n", car1.brand, car1.model, car1.year);
  printf("%s %s %d\n", car2.brand, car2.model, car2.year);
  printf("%s %s %d\n", car3.brand, car3.model, car3.year);

  return 0;
}
</code></pre>
                    <!-- Try it Yourself example -->

                    <div class="mt-4">
                        <a href="<?= base_url('discussion') ?>" class="btn btn-outline-primary">Back to Topics</a>
                        <!-- <button id="downloadPdfBtn" class="btn btn-outline-danger">
                            <i class="fa fa-file-pdf-o mr-1"></i> Download as PDF
                        </button> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>
<script src="<?= base_url('assets/jspdf.umd.min.js') ?>"></script>

<script>
    hljs.highlightAll();

    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        var discussionContent = document.querySelector('.content');
        html2canvas(discussionContent, {
            scale: 1
        }).then(function(canvas) {
            var imgData = canvas.toDataURL('image/jpeg', 0.8);
            var pdf = new window.jspdf.jsPDF('p', 'mm', 'a4');
            var pageWidth = pdf.internal.pageSize.getWidth();
            var pageHeight = pdf.internal.pageSize.getHeight();
            var imgWidth = pageWidth - 20;
            var imgHeight = canvas.height * imgWidth / canvas.width;

            var position = 10;

            if (imgHeight <= pageHeight - 20) {
                pdf.addImage(imgData, 'JPEG', 10, position, imgWidth, imgHeight);
            } else {
                var pageCanvas = document.createElement('canvas');
                var pageCtx = pageCanvas.getContext('2d');
                var pageHeightPx = (canvas.width / imgWidth) * (pageHeight - 20);

                var renderedHeight = 0;
                while (renderedHeight < canvas.height) {
                    pageCanvas.width = canvas.width;
                    pageCanvas.height = pageHeightPx;

                    pageCtx.fillStyle = "#fff";
                    pageCtx.fillRect(0, 0, pageCanvas.width, pageCanvas.height);

                    pageCtx.drawImage(canvas, 0, renderedHeight, canvas.width, pageHeightPx, 0, 0, canvas.width, pageHeightPx);

                    var pageImgData = pageCanvas.toDataURL('image/jpeg', 0.8);
                    pdf.addImage(pageImgData, 'JPEG', 10, 10, imgWidth, pageHeight - 20);

                    renderedHeight += pageHeightPx;
                    if (renderedHeight < canvas.height) {
                        pdf.addPage();
                    }
                }
            }
            pdf.save('discussion_structs.pdf');
        });
    });
</script>

<?php $this->load->view('footer'); ?>